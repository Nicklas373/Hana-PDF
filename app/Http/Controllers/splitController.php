<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\extract_pdf;
use App\Models\split_pdf;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Ilovepdf\Ilovepdf;
use Ilovepdf\Exceptions;
use Spatie\PdfToImage\Pdf;

class splitController extends Controller
{
    public function pdf_split(Request $request): RedirectResponse{
		$validator = Validator::make($request->all(),[
			'file' => 'mimes:pdf|max:25000',
			'fileAlt' => ''
		]);

        $uuid = AppHelper::Instance()->get_guid();

		if($validator->fails()) {
            return redirect()->back()->withErrors(['error'=>$validator->messages(), 'uuid'=>$uuid])->withInput();
        } else {
			if(isset($_POST['formAction']))
			{
				if($request->post('formAction') == "upload") {
					if($request->hasfile('file')) {
						$str = rand();
						$pdfUpload_Location = env('PDF_UPLOAD');
                        $file = $request->file('file');
                        $randomizePdfFileName = md5($str);
                        $randomizePdfPath = $pdfUpload_Location.'/'.$randomizePdfFileName.'.pdf';
						$pdfFileName = $file->getClientOriginalName();
                        $pdfTotalPages = AppHelper::instance()->count($file);
                        $file->storeAs('public/upload-pdf', $randomizePdfFileName.'.pdf');
						if (Storage::disk('local')->exists('public/'.$randomizePdfPath)) {
							$pdf = new Pdf(Storage::disk('local')->path('public/'.$randomizePdfPath));
							$pdf->setPage(1)
								->setOutputFormat('png')
								->width(400)
								->saveImage(Storage::disk('local')->path('public/'.env('PDF_THUMBNAIL')));
							if (Storage::disk('local')->exists('public/'.env('PDF_THUMBNAIL').'/1.png')) {
                                Storage::disk('local')->move('public/'.env('PDF_THUMBNAIL').'/1.png', 'public/'.env('PDF_THUMBNAIL').'/'.$randomizePdfFileName.'.png');
                                return redirect()->back()->with([
                                    'status' => true,
                                    'pdfRndmName' => Storage::disk('local')->url(env('PDF_UPLOAD').'/'.$randomizePdfFileName.'.pdf'),
                                    'pdfThumbName' => Storage::disk('local')->url(env('PDF_THUMBNAIL').'/'.$randomizePdfFileName.'.png'),
                                    'pdfOriName' => $pdfFileName,
                                    'pdfTotalPages' => $pdfTotalPages
                                ]);
							} else {
								return redirect()->back()->withErrors(['error'=>'Thumbnail failed to generated !', 'uuid'=>$uuid])->withInput();
							}
						} else {
                            return redirect()->back()->withErrors(['error'=>'PDF file not found on the server !', 'uuid'=>$uuid])->withInput();
						}
					} else {
                        return redirect()->back()->withErrors(['error'=>'PDF failed to upload !', 'uuid'=>$uuid])->withInput();
					}
				} else if ($request->post('formAction') == "split") {
					if(isset($_POST['fileAlt'])) {
						$file = $request->post('fileAlt');

                        $pdfUpload_Location = env('PDF_UPLOAD');
                        $pdfProcessed_Location = env('PDF_DOWNLOAD');
						$pdfName = basename($file);
                        $pdfNameWithoutExtension = basename($pdfName, '.pdf');
                        $pdfNewPath = Storage::disk('local')->path('public/'.$pdfUpload_Location.'/'.$pdfName);
                        $thumbName = Storage::disk('local')->path('public/'.env('PDF_THUMBNAIL').'/'.basename($pdfName, ".pdf").'.png');
						$fileSize = filesize($pdfNewPath);
						$hostName = AppHelper::instance()->getUserIpAddr();
						$newFileSize = AppHelper::instance()->convert($fileSize, "MB");

						if(isset($_POST['fromPage']))
						{
							$fromPage = $request->post('fromPage');
						} else {
							$fromPage = '';
						}

						if(isset($_POST['toPage']))
						{
							$toPage = $request->post('toPage');
						} else {
							$toPage = '';
						}

						if(isset($_POST['mergePDF']))
						{
							$tempPDF = $request->post('mergePDF');
							$tempCompare = $tempPDF ? 'true' : 'false';
							$mergeDBpdf = "true";
							$mergePDF = filter_var($tempCompare, FILTER_VALIDATE_BOOLEAN);
						} else {
							$tempCompare = false ? 'true' : 'false';
							$mergeDBpdf = "false";
							$mergePDF = filter_var($tempCompare, FILTER_VALIDATE_BOOLEAN);
						}

						if(isset($_POST['fixedPage']))
						{
							$fixedPage = $request->post('fixedPage');
						} else {
							$fixedPage = '';
						}

						if(isset($_POST['customPage']))
						{
							$customPage = $request->post('customPage');
						} else {
							$customPage = '';
						}

						if ($fromPage != ''){
							$pdfTotalPages = AppHelper::instance()->count($pdfNewPath);
							if ($toPage > $pdfTotalPages) {
								return redirect()->back()->withErrors([
                                    'error'=>'To Page selected value has more than total PDF pages ! (total pages: '.$pdfTotalPages.')',
                                    'uuid'=>$uuid
                                    ])->withInput();
                            } else if ($fromPage > $pdfTotalPages) {
                                return redirect()->back()->withErrors([
                                    'error'=>'From Page selected value has more than total PDF pages ! (total pages: '.$pdfTotalPages.')',
                                    'uuid'=>$uuid
                                    ])->withInput();
                            } else if ($fromPage > $toPage) {
								return redirect()->back()->withErrors([
                                    'error'=>'First Page value has more than To Page value !',
                                    'uuid'=>$uuid
                                    ])->withInput();
							} else {
								if ($mergeDBpdf == "true") {
									$fixedPageRanges = $fromPage.'-'.$toPage;
								} else if ($mergeDBpdf == "false") {
									$pdfStartPages = $fromPage;
									$pdfTotalPages = $toPage;
									while($pdfStartPages <= intval($pdfTotalPages))
									{
										$pdfArrayPages[] = $pdfStartPages;
										$pdfStartPages += 1;
									}
									$fixedPageRanges = implode(', ', $pdfArrayPages);
								}
							}
						} else {
							$fixedPageRanges = $customPage;
						};

                        try {
                            $ilovepdf = new Ilovepdf(env('ILOVEPDF_PUBLIC_KEY'),env('ILOVEPDF_SECRET_KEY'));
                            $ilovepdfTask = $ilovepdf->newTask('split');
                            $ilovepdfTask->setFileEncryption(env('ILOVEPDF_ENC_KEY'));
                            $pdfFile = $ilovepdfTask->addFile($pdfNewPath);
                            $ilovepdfTask->setRanges($fixedPageRanges);
                            $ilovepdfTask->setMergeAfter($mergePDF);
                            $ilovepdfTask->setPackagedFilename($pdfNameWithoutExtension);
                            $ilovepdfTask->setOutputFileName($pdfNameWithoutExtension);
                            $ilovepdfTask->execute();
                            $ilovepdfTask->download(Storage::disk('local')->path('public/'.$pdfProcessed_Location));
                        } catch (\Ilovepdf\Exceptions\StartException $e) {
                            DB::table('split_pdfs')->insert([
                                'fileName' => $pdfName,
                                'fileSize' => $newFileSize,
                                'fromPage' => $fromPage,
                                'toPage' => $toPage,
                                'customPage' => $customPage,
                                'fixedPage' => $fixedPage,
                                'fixedPageRange' => $fixedPageRanges,
                                'hostName' => $hostName,
                                'mergePDF' => $mergeDBpdf,
                                'result' => false,
                                'err_reason' => 'iLovePDF API Error !, Catch on StartException',
                                'err_api_reason' => $e->getMessage(),
                                'uuid' => $uuid,
                                'created_at' => AppHelper::instance()->getCurrentTimeZone()
                            ]);
                            return redirect()->back()->withErrors(['error'=>'iLovePDF API Error !', 'uuid'=>$uuid])->withInput();
                        } catch (\Ilovepdf\Exceptions\AuthException $e) {
                            DB::table('split_pdfs')->insert([
                                'fileName' => $pdfName,
                                'fileSize' => $newFileSize,
                                'fromPage' => $fromPage,
                                'toPage' => $toPage,
                                'customPage' => $customPage,
                                'fixedPage' => $fixedPage,
                                'fixedPageRange' => $fixedPageRanges,
                                'hostName' => $hostName,
                                'mergePDF' => $mergeDBpdf,
                                'result' => false,
                                'err_reason' => 'iLovePDF API Error !, Catch on AuthException',
                                'err_api_reason' => $e->getMessage(),
                                'uuid' => $uuid,
                                'created_at' => AppHelper::instance()->getCurrentTimeZone()
                            ]);
                            return redirect()->back()->withErrors(['error'=>'iLovePDF API Error !', 'uuid'=>$uuid])->withInput();
                        } catch (\Ilovepdf\Exceptions\UploadException $e) {
                            DB::table('split_pdfs')->insert([
                                'fileName' => $pdfName,
                                'fileSize' => $newFileSize,
                                'fromPage' => $fromPage,
                                'toPage' => $toPage,
                                'customPage' => $customPage,
                                'fixedPage' => $fixedPage,
                                'fixedPageRange' => $fixedPageRanges,
                                'hostName' => $hostName,
                                'mergePDF' => $mergeDBpdf,
                                'result' => false,
                                'err_reason' => 'iLovePDF API Error !, Catch on UploadException',
                                'err_api_reason' => $e->getMessage(),
                                'uuid' => $uuid,
                                'created_at' => AppHelper::instance()->getCurrentTimeZone()
                            ]);
                            return redirect()->back()->withErrors(['error'=>'iLovePDF API Error !', 'uuid'=>$uuid])->withInput();
                        } catch (\Ilovepdf\Exceptions\ProcessException $e) {
                            DB::table('split_pdfs')->insert([
                                'fileName' => $pdfName,
                                'fileSize' => $newFileSize,
                                'fromPage' => $fromPage,
                                'toPage' => $toPage,
                                'customPage' => $customPage,
                                'fixedPage' => $fixedPage,
                                'fixedPageRange' => $fixedPageRanges,
                                'hostName' => $hostName,
                                'mergePDF' => $mergeDBpdf,
                                'result' => false,
                                'err_reason' => 'iLovePDF API Error !, Catch on ProcessException',
                                'err_api_reason' => $e->getMessage(),
                                'uuid' => $uuid,
                                'created_at' => AppHelper::instance()->getCurrentTimeZone()
                            ]);
                            return redirect()->back()->withErrors(['error'=>'iLovePDF API Error !', 'uuid'=>$uuid])->withInput();
                        } catch (\Ilovepdf\Exceptions\DownloadException $e) {
                            DB::table('split_pdfs')->insert([
                                'fileName' => $pdfName,
                                'fileSize' => $newFileSize,
                                'fromPage' => $fromPage,
                                'toPage' => $toPage,
                                'customPage' => $customPage,
                                'fixedPage' => $fixedPage,
                                'fixedPageRange' => $fixedPageRanges,
                                'hostName' => $hostName,
                                'mergePDF' => $mergeDBpdf,
                                'result' => false,
                                'err_reason' => 'iLovePDF API Error !, Catch on DownloadException',
                                'err_api_reason' => $e->getMessage(),
                                'uuid' => $uuid,
                                'created_at' => AppHelper::instance()->getCurrentTimeZone()
                            ]);
                            return redirect()->back()->withErrors(['error'=>'iLovePDF API Error !', 'uuid'=>$uuid])->withInput();
                        } catch (\Ilovepdf\Exceptions\TaskException $e) {
                            DB::table('split_pdfs')->insert([
                                'fileName' => $pdfName,
                                'fileSize' => $newFileSize,
                                'fromPage' => $fromPage,
                                'toPage' => $toPage,
                                'customPage' => $customPage,
                                'fixedPage' => $fixedPage,
                                'fixedPageRange' => $fixedPageRanges,
                                'hostName' => $hostName,
                                'mergePDF' => $mergeDBpdf,
                                'result' => false,
                                'err_reason' => 'iLovePDF API Error !, Catch on TaskException',
                                'err_api_reason' => $e->getMessage(),
                                'uuid' => $uuid,
                                'created_at' => AppHelper::instance()->getCurrentTimeZone()
                            ]);
                            return redirect()->back()->withErrors(['error'=>'iLovePDF API Error !', 'uuid'=>$uuid])->withInput();
                        } catch (\Ilovepdf\Exceptions\PathException $e) {
                            DB::table('split_pdfs')->insert([
                                'fileName' => $pdfName,
                                'fileSize' => $newFileSize,
                                'fromPage' => $fromPage,
                                'toPage' => $toPage,
                                'customPage' => $customPage,
                                'fixedPage' => $fixedPage,
                                'fixedPageRange' => $fixedPageRanges,
                                'hostName' => $hostName,
                                'mergePDF' => $mergeDBpdf,
                                'result' => false,
                                'err_reason' => 'iLovePDF API Error !, Catch on PathException',
                                'err_api_reason' => $e->getMessage(),
                                'uuid' => $uuid,
                                'created_at' => AppHelper::instance()->getCurrentTimeZone()
                            ]);
                            return redirect()->back()->withErrors(['error'=>'iLovePDF API Error !', 'uuid'=>$uuid])->withInput();
                        } catch (\Exception $e) {
                            DB::table('split_pdfs')->insert([
                                'fileName' => $pdfName,
                                'fileSize' => $newFileSize,
                                'fromPage' => $fromPage,
                                'toPage' => $toPage,
                                'customPage' => $customPage,
                                'fixedPage' => $fixedPage,
                                'fixedPageRange' => $fixedPageRanges,
                                'hostName' => $hostName,
                                'mergePDF' => $mergeDBpdf,
                                'result' => false,
                                'err_reason' => 'iLovePDF API Error !, Catch on Exception',
                                'err_api_reason' => $e->getMessage(),
                                'uuid' => $uuid,
                                'created_at' => AppHelper::instance()->getCurrentTimeZone()
                            ]);
                            return redirect()->back()->withErrors(['error'=>'iLovePDF API Error !', 'uuid'=>$uuid])->withInput();
                        }

                        if (file_exists($pdfNewPath)) {
                            unlink($pdfNewPath);
                        }

                        if (file_exists($thumbName)) {
                            unlink($thumbName);
                        }

                        if (file_exists(Storage::disk('local')->path('public/'.$pdfProcessed_Location.'/'.$pdfNameWithoutExtension.'.zip'))) {
                            $download_pdf = Storage::disk('local')->url($pdfProcessed_Location.'/'.$pdfNameWithoutExtension.'.zip');
                            DB::table('split_pdfs')->insert([
                                'fileName' => $pdfName,
                                'fileSize' => $newFileSize,
                                'fromPage' => $fromPage,
                                'toPage' => $toPage,
                                'customPage' => $customPage,
                                'fixedPage' => $fixedPage,
                                'fixedPageRange' => $fixedPageRanges,
                                'hostName' => $hostName,
                                'mergePDF' => $mergeDBpdf,
                                'result' => true,
                                'err_reason' => null,
                                'err_api_reason' => null,
                                'uuid' => $uuid,
                                'created_at' => AppHelper::instance()->getCurrentTimeZone()
                            ]);
                            return redirect()->back()->with([
                                "stats" => "scs",
                                "res"=>$download_pdf,
                            ]);
                        } else if (file_existsStorage::disk('local')->path('public/'.$pdfProcessed_Location.'/'.$pdfName)) {
                            $download_pdf = Storage::disk('local')->url($pdfProcessed_Location.'/'.$pdfName);
                            DB::table('split_pdfs')->insert([
                                'fileName' => $pdfName,
                                'fileSize' => $newFileSize,
                                'fromPage' => $fromPage,
                                'toPage' => $toPage,
                                'customPage' => $customPage,
                                'fixedPage' => $fixedPage,
                                'fixedPageRange' => $fixedPageRanges,
                                'hostName' => $hostName,
                                'mergePDF' => $mergeDBpdf,
                                'result' => true,
                                'err_reason' => null,
                                'err_api_reason' => null,
                                'uuid' => $uuid,
                                'created_at' => AppHelper::instance()->getCurrentTimeZone()
                            ]);
                            return redirect()->back()->with([
                                "stats" => "scs",
                                "res"=>$download_pdf,
                            ]);
                        } else {
                            DB::table('split_pdfs')->insert([
                                'fileName' => $pdfName,
                                'fileSize' => $newFileSize,
                                'fromPage' => $fromPage,
                                'toPage' => $toPage,
                                'customPage' => $customPage,
                                'fixedPage' => $fixedPage,
                                'fixedPageRange' => $fixedPageRanges,
                                'hostName' => $hostName,
                                'mergePDF' => $mergeDBpdf,
                                'result' => false,
                                'err_reason' => 'Failed to download file from iLovePDF API !',
                                'err_api_reason' => null,
                                'uuid' => $uuid,
                                'created_at' => AppHelper::instance()->getCurrentTimeZone()
                            ]);
                            return redirect()->back()->withErrors(['error'=>'Failed to download file from iLovePDF API !', 'uuid'=>$uuid])->withInput();
                        }
					} else {
                        return redirect()->back()->withErrors(['error'=>'PDF failed to upload !', 'uuid'=>$uuid])->withInput();
					}
				} else if ($request->post('formAction') == "extract") {
                    if(isset($_POST['fileAlt'])) {
                        $file = $request->post('fileAlt');

                        if(isset($_POST['customPage']))
						{
							$customPage = $request->post('customPage');
						} else {
							$customPage = '';
						}

                        $pdfNewRanges = $customPage;

                        $pdfName = basename($file);
                        $pdfNameWithoutExtension = basename($pdfName, '.pdf');
                        $pdfNewPath = Storage::disk('local')->path('public/'.$pdfUpload_Location.'/'.$pdfName);
                        $thumbName = Storage::disk('local')->path('public/'.env('PDF_THUMBNAIL').'/'.basename($pdfName, ".pdf").'.png');
						$fileSize = filesize($pdfNewPath);
						$hostName = AppHelper::instance()->getUserIpAddr();
						$newFileSize = AppHelper::instance()->convert($fileSize, "MB");

                        try {
                            $ilovepdf = new Ilovepdf(env('ILOVEPDF_PUBLIC_KEY'),env('ILOVEPDF_SECRET_KEY'));
                            $ilovepdfTask = $ilovepdf->newTask('split');
                            $ilovepdfTask->setFileEncryption(env('ILOVEPDF_ENC_KEY'));
                            $pdfFile = $ilovepdfTask->addFile($pdfNewPath);
                            $ilovepdfTask->setRanges($pdfNewRanges);
                            $ilovepdfTask->setMergeAfter(false);
                            $ilovepdfTask->setPackagedFilename($pdfNameWithoutExtension);
                            $ilovepdfTask->setOutputFileName($pdfNameWithoutExtension);
                            $ilovepdfTask->execute();
                            $ilovepdfTask->download(Storage::disk('local')->path('public/'.$pdfProcessed_Location));
                        } catch (\Ilovepdf\Exceptions\StartException $e) {
                            DB::table('extract_pdfs')->insert([
                                'fileName' => $pdfName,
                                'fileSize' => $newFileSize,
                                'customPage' => $customPage,
                                'hostName' => $hostName,
                                'result' => false,
                                'err_reason' => 'iLovePDF API Error !, Catch on StartException',
                                'err_api_reason' => $e->getMessage(),
                                'uuid' => $uuid,
                                'created_at' => AppHelper::instance()->getCurrentTimeZone()
                            ]);
                            return redirect()->back()->withErrors(['error'=>'iLovePDF API Error !', 'uuid'=>$uuid])->withInput();
                        } catch (\Ilovepdf\Exceptions\AuthException $e) {
                            DB::table('extract_pdfs')->insert([
                                'fileName' => $pdfName,
                                'fileSize' => $newFileSize,
                                'customPage' => $customPage,
                                'hostName' => $hostName,
                                'result' => false,
                                'err_reason' => 'iLovePDF API Error !, Catch on AuthException',
                                'err_api_reason' => $e->getMessage(),
                                'uuid' => $uuid,
                                'created_at' => AppHelper::instance()->getCurrentTimeZone()
                            ]);
                            return redirect()->back()->withErrors(['error'=>'iLovePDF API Error !', 'uuid'=>$uuid])->withInput();
                        } catch (\Ilovepdf\Exceptions\UploadException $e) {
                            DB::table('extract_pdfs')->insert([
                                'fileName' => $pdfName,
                                'fileSize' => $newFileSize,
                                'customPage' => $customPage,
                                'hostName' => $hostName,
                                'result' => false,
                                'err_reason' => 'iLovePDF API Error !, Catch on UploadException',
                                'err_api_reason' => $e->getMessage(),
                                'uuid' => $uuid,
                                'created_at' => AppHelper::instance()->getCurrentTimeZone()
                            ]);
                            return redirect()->back()->withErrors(['error'=>'iLovePDF API Error !', 'uuid'=>$uuid])->withInput();
                        } catch (\Ilovepdf\Exceptions\ProcessException $e) {
                            DB::table('extract_pdfs')->insert([
                                'fileName' => $pdfName,
                                'fileSize' => $newFileSize,
                                'customPage' => $customPage,
                                'hostName' => $hostName,
                                'result' => false,
                                'err_reason' => 'iLovePDF API Error !, Catch on ProcessException',
                                'err_api_reason' => $e->getMessage(),
                                'uuid' => $uuid,
                                'created_at' => AppHelper::instance()->getCurrentTimeZone()
                            ]);
                            return redirect()->back()->withErrors(['error'=>'iLovePDF API Error !', 'uuid'=>$uuid])->withInput();
                        } catch (\Ilovepdf\Exceptions\DownloadException $e) {
                            DB::table('extract_pdfs')->insert([
                                'fileName' => $pdfName,
                                'fileSize' => $newFileSize,
                                'customPage' => $customPage,
                                'hostName' => $hostName,
                                'result' => false,
                                'err_reason' => 'iLovePDF API Error !, Catch on DownloadException',
                                'err_api_reason' => $e->getMessage(),
                                'uuid' => $uuid,
                                'created_at' => AppHelper::instance()->getCurrentTimeZone()
                            ]);
                            return redirect()->back()->withErrors(['error'=>'iLovePDF API Error !', 'uuid'=>$uuid])->withInput();
                        } catch (\Ilovepdf\Exceptions\TaskException $e) {
                            DB::table('extract_pdfs')->insert([
                                'fileName' => $pdfName,
                                'fileSize' => $newFileSize,
                                'customPage' => $customPage,
                                'hostName' => $hostName,
                                'result' => false,
                                'err_reason' => 'iLovePDF API Error !, Catch on TaskException',
                                'err_api_reason' => $e->getMessage(),
                                'uuid' => $uuid,
                                'created_at' => AppHelper::instance()->getCurrentTimeZone()
                            ]);
                            return redirect()->back()->withErrors(['error'=>'iLovePDF API Error !', 'uuid'=>$uuid])->withInput();
                        } catch (\Ilovepdf\Exceptions\PathException $e) {
                            DB::table('extract_pdfs')->insert([
                                'fileName' => $pdfName,
                                'fileSize' => $newFileSize,
                                'customPage' => $customPage,
                                'hostName' => $hostName,
                                'result' => false,
                                'err_reason' => 'iLovePDF API Error !, Catch on PathException',
                                'err_api_reason' => $e->getMessage(),
                                'uuid' => $uuid,
                                'created_at' => AppHelper::instance()->getCurrentTimeZone()
                            ]);
                            return redirect()->back()->withErrors(['error'=>'iLovePDF API Error !', 'uuid'=>$uuid])->withInput();
                        } catch (\Exception $e) {
                            DB::table('extract_pdfs')->insert([
                                'fileName' => $pdfName,
                                'fileSize' => $newFileSize,
                                'customPage' => $customPage,
                                'hostName' => $hostName,
                                'result' => false,
                                'err_reason' => 'iLovePDF API Error ! Catch on Exception',
                                'err_api_reason' => $e->getMessage(),
                                'uuid' => $uuid,
                                'created_at' => AppHelper::instance()->getCurrentTimeZone()
                            ]);
                            return redirect()->back()->withErrors(['error'=>'iLovePDF API Error !', 'uuid'=>$uuid])->withInput();
                        }

                        if (file_exists($pdfNewPath)) {
                            unlink($pdfNewPath);
                        }

                        if (file_exists($thumbName)) {
                            unlink($thumbName);
                        }

                        if (file_exists(Storage::disk('local')->path('public/'.$pdfProcessed_Location.'/'.$pdfName))) {
                            $download_pdf = Storage::disk('local')->url($pdfProcessed_Location.'/'.$pdfName);

                            DB::table('extract_pdfs')->insert([
                                'fileName' => $pdfName,
                                'fileSize' => $newFileSize,
                                'customPage' => $customPage,
                                'hostName' => $hostName,
                                'result' => true,
                                'err_reason' => null,
                                'err_api_reason' => null,
                                'uuid' => $uuid,
                                'created_at' => AppHelper::instance()->getCurrentTimeZone()
                            ]);
                            return redirect()->back()->with([
                                "stats" => "scs",
                                "res"=>$download_pdf,
                            ]);
                        } else if (file_exists(Storage::disk('local')->path('public/'.$pdfProcessed_Location.'/'.$pdfNameWithoutExtension.'.zip'))) {
                            $download_pdf = Storage::disk('local')->url($pdfProcessed_Location.'/'.$pdfNameWithoutExtension.'.zip');
                            DB::table('extract_pdfs')->insert([
                                'fileName' => $pdfName,
                                'fileSize' => $newFileSize,
                                'customPage' => $customPage,
                                'hostName' => $hostName,
                                'result' => true,
                                'err_reason' => null,
                                'err_api_reason' => null,
                                'uuid' => $uuid,
                                'created_at' => AppHelper::instance()->getCurrentTimeZone()
                            ]);
                            return redirect()->back()->with([
                                "stats" => "scs",
                                "res"=>$download_pdf,
                            ]);
                        } else {
                            DB::table('extract_pdfs')->insert([
                                'fileName' => $pdfName,
                                'fileSize' => $newFileSize,
                                'customPage' => $customPage,
                                'hostName' => $hostName,
                                'result' => false,
                                'err_reason' => 'Failed to download file from iLovePDF API !',
                                'err_api_reason' => null,
                                'uuid' => $uuid,
                                'created_at' => AppHelper::instance()->getCurrentTimeZone()
                            ]);
                            return redirect()->back()->withErrors(['error'=>'Failed to download file from iLovePDF API !', 'uuid'=>$uuid])->withInput();
                        }
                    } else {
                        return redirect()->back()->withErrors(['error'=>'PDF failed to upload !', 'uuid'=>$uuid])->withInput();
                    }
				} else {
                    return redirect()->back()->withErrors(['error'=>'INVALID_REQUEST_ERROR !', 'uuid'=>$uuid])->withInput();
                }
			} else {
				return redirect()->back()->withErrors(['error'=>'ERROR_OUT_OF_BOUND !', 'uuid'=>$uuid])->withInput();
			}
		}
    }
}
