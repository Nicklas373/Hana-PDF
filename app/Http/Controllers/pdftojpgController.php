<?php
 
namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\pdf_jpg;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Ilovepdf\PdfjpgTask;
use Spatie\PdfToImage\Pdf;

class pdftojpgController extends Controller
{
    public function image(){
		return view('pdftojpg');
	}

    public function pdf_image(Request $request): RedirectResponse{
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
					if($request->hasfile('file')) {
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
								return redirect()->back()->with('upload','/'.env('pdf_thumbnail').'/'.$pdfNameWithoutExtension.'.png');
							} else {
								return redirect()->back()->withError('error',' has failed to upload !')->withInput();
							}
						} else {
							return redirect()->back()->withError('error',' has failed to upload !')->withInput();
						}
					} else {
						return redirect()->back()->withError('error',' FILE NOT FOUND !')->withInput();
					}
				} else if ($request->post('formAction') == "convert") {
					if(isset($_POST['fileAlt'])) {
						$pdfUpload_Location = env('pdf_upload');
						$file = $request->post('fileAlt');
						$pdfProcessed_Location = 'temp';
						$pdfName = basename($request->post('fileAlt'));
						$pdfNameWithoutExtension = basename($pdfName, ".pdf");
						$fileSize = filesize($request->post('fileAlt'));
						$hostName = AppHelper::instance()->getUserIpAddr();
						$newFileSize = AppHelper::instance()->convert($fileSize, "MB");
                
                        pdf_jpg::create([
							'fileName' => $pdfName,
							'fileSize' => $newFileSize,
							'hostName' => $hostName
						]);
			
						$ilovepdfTask = new PdfjpgTask(env('ILOVEPDF_PUBLIC_KEY'),env('ILOVEPDF_SECRET_KEY'));
						$ilovepdfTask->setFileEncryption(env('ILOVEPDF_ENC_KEY'));
						$pdfFile = $ilovepdfTask->addFile($file);
						$ilovepdfTask->setMode('pages');
						$ilovepdfTask->setOutputFileName($pdfName);
						$ilovepdfTask->setPackagedFilename($pdfNameWithoutExtension);
						$ilovepdfTask->execute();
						$ilovepdfTask->download($pdfProcessed_Location);
						
						if(is_file($request->post('fileAlt'))) {
							unlink($request->post('fileAlt'));
						}
						
						$download_pdf = $pdfProcessed_Location.'/'.$pdfNameWithoutExtension.'.zip';

						if (file_exists($download_pdf)) {
							return redirect()->back()->with('success',$download_pdf);
						} else {
							$download_pdf = $pdfProcessed_Location.'/'.$pdfNameWithoutExtension.'-0001.jpg';
							if (file_exists($download_pdf)) {
								return redirect()->back()->with('success',$download_pdf);
							} else {
								return redirect()->back()->withError('error',' has failed to convert !')->withInput();
							}
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