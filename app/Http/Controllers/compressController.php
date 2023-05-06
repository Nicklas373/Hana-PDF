<?php
 
namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\compression_pdf;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Ilovepdf\Ilovepdf;
use Spatie\PdfToImage\Pdf;

class compressController extends Controller
{
	public function compress(){
		return view('compress');
	}

	public function pdf_init(Request $request): RedirectResponse{
		$validator = Validator::make($request->all(),[
			'file' => 'mimes:pdf|max:25000',
			'fileAlt' => ''
		]);
 
		if($validator->fails()) {
			return redirect()->back()->withErrors($validator->messages())->withInput();
		} else {
			if(isset($_POST['formAction']))
			{
				if($request->post('formAction') == "upload") {
					if(isset($_POST['file'])) {
						$pdfUpload_Location = env('pdf_upload');
						$file = $request->file('file');
						$file->move($pdfUpload_Location,$file->getClientOriginalName());
						$pdfFileName = $pdfUpload_Location.'/'.$file->getClientOriginalName();
						$pdfNameWithoutExtension = basename($file->getClientOriginalName(), '.pdf');

						if (file_exists($pdfFileName)) {
							$pdf = new Pdf($pdfFileName);
							$pdf->setPage(1)
								->setOutputFormat('png')
								->width(400)
								->saveImage(env('pdf_thumbnail'));
							if (file_exists(env('pdf_thumbnail').'/1.png')) {
								$thumbnail = file(env('pdf_thumbnail').'/1.png');
								rename(env('pdf_thumbnail').'/1.png', env('pdf_thumbnail').'/'.$pdfNameWithoutExtension.'.png');
								return redirect()->back()->with('success','/'.env('pdf_thumbnail').'/'.$pdfNameWithoutExtension.'.png');
							} else {
								return redirect()->back()->withError('error',' has failed to upload !')->withInput();
							}
						} else {
							return redirect()->back()->withError('error',' has failed to upload !')->withInput();
						}
					} else {
						return redirect()->back()->withError('error',' FILE NOT FOUND !')->withInput();
					}
				} else if ($request->post('formAction') == "compress") {
					if(isset($_POST['fileAlt'])) {
						if(isset($_POST['compMethod']))
						{
							$compMethod = $request->post('compMethod');
						} else {
							$compMethod = "recommended";
						}
			
						$pdfProcessed_Location = 'temp';
						$pdfName = basename($request->post('fileAlt'));
						$pdfNameWithoutExtension = basename($request->post('fileAlt'), ".pdf");
						$fileSize = filesize($request->post('fileAlt'));
						$hostName = gethostname();
						$newFileSize = AppHelper::instance()->convert($fileSize, "MB");
			
						compression_pdf::create([
							'fileName' => $request->post('fileAlt'),
							'fileSize' => $newFileSize,
							'compMethod' => $compMethod,
							'hostName' => $hostName
						]);
			
						$ilovepdf = new Ilovepdf(env('ILOVEPDF_PUBLIC_KEY'),env('ILOVEPDF_SECRET_KEY'));
						$ilovepdfTask = $ilovepdf->newTask('compress');
						$ilovepdfTask->setFileEncryption(env('ILOVEPDF_ENC_KEY'));
						$pdfFile = $ilovepdfTask->addFile($request->post('fileAlt'));
						$ilovepdfTask->setOutputFileName($pdfName);
						$ilovepdfTask->setCompressionLevel($compMethod);
						$ilovepdfTask->execute();
						$ilovepdfTask->download($pdfProcessed_Location);
						
						$download_pdf = $pdfProcessed_Location.'/'.$pdfName;
						
						if(is_file($request->post('fileAlt'))) {
							unlink($request->post('fileAlt'));
						}
			
						if (file_exists($download_pdf)) {
							return redirect()->back()->with('success',$download_pdf);
						} else {
							return redirect()->back()->withError('error',' has failed to compress !')->withInput();
						}
					} else {
						return redirect()->back()->withError('error',' REQUEST NOT FOUND !')->withInput();
					}
				} else {
					return redirect()->back()->withError('error',' FILE NOT FOUND !')->withInput();
				}
			} else {
				return redirect()->back()->withError('error',' REQUEST NOT FOUND !')->withInput();
			}
		}
	}
}