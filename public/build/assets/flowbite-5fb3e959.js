import{M as m}from"./index-f475cd16.js";const y=document.getElementById("loadingModal"),h=document.getElementById("errModal"),u={placement:"bottom-right",backdrop:"dynamic",backdropClasses:"bg-gray-900 bg-opacity-50 dark:bg-opacity-80 backdrop-filter backdrop-blur-sm fixed inset-0 z-40",closable:!0,onHide:()=>{},onShow:()=>{},onToggle:()=>{}},o=new m(y,u),i=new m(h,u);let v=document.querySelector("form");v.onsubmit=function(t){var n=document.getElementById("file_input"),c=document.getElementById("filelist"),e=document.getElementById("errMessageModal"),l=document.getElementById("errSubMessageModal");if(c!==null){for(var a=document.getElementById("multiple_files").files,f=!1,r=0,d=0;d<a.length;d++){var p=a[d];let s=p.size;p.type=="application/pdf"?s>=25e6&&r++:(r++,f=!0)}r>0?f?(t.preventDefault(),e.innerHTML="Unsupported file format !",l.innerHTML="Supported file format: pdf",i.show()):(t.preventDefault(),e.innerHTML="Uploaded file has exceeds the limit !",l.style.visibility=null,i.show()):(e.style.visibility=null,l.style.visibility=null,i.hide(),o.show())}else{let s=n.files[0].size;document.getElementById("cnvFrPDF")!==null?n.files[0].type=="application/pdf"?s>=25e6?(t.preventDefault(),e.innerHTML="Uploaded file has exceeds the limit !",l.style.visibility=null,i.show()):(e.style.visibility=null,l.style.visibility=null,i.hide(),o.show()):(t.preventDefault(),e.innerHTML="Unsupported file format !",l.innerHTML="Supported file format: pdf",i.show()):document.getElementById("cnvToPDF")!==null?n.files[0].type=="application/vnd.openxmlformats-officedocument.wordprocessingml.document"||n.files[0].type=="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"||n.files[0].type=="application/vnd.openxmlformats-officedocument.presentationml.presentation"?s>=25e6?(t.preventDefault(),e.innerHTML="Uploaded file has exceeds the limit !",l.style.visibility=null,i.show()):(e.style.visibility=null,l.style.visibility=null,i.hide(),o.show()):(t.preventDefault(),e.innerHTML="Unsupported file format !",l.innerHTML="Supported file format: docx, xlsx, pptx",i.show()):n.files[0].type=="application/pdf"?s>=25e6?(t.preventDefault(),e.innerHTML="Uploaded file has exceeds the limit !",l.style.visibility=null,i.show()):(e.style.visibility=null,l.style.visibility=null,i.hide(),o.show()):(t.preventDefault(),e.innerHTML="Unsupported file format !",l.innerHTML="Supported file format: pdf",i.show())}};