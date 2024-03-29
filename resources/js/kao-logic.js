import { Modal } from 'flowbite'
import { Dropzone } from "dropzone"

const $errModal = document.getElementById('errModal')
const $loadingModal = document.getElementById('loadingModal')
const $previewModal = document.getElementById('previewModal')
const $previewDocumentModal = document.getElementById('previewDocumentModal')
const $previewImageModal = document.getElementById('previewImgModal')

const options = {
    placement: 'bottom-right',
    backdrop: 'static',
    backdropClasses: 'bg-gray-900 bg-opacity-50 backdrop-filter backdrop-blur-sm fixed inset-0 z-40',
    closable: true,
    onHide: () => {
        //console.log('modal is hidden')
    },
    onShow: () => {
        //console.log('modal is shown')
    },
    onToggle: () => {
        //console.log('modal has been toggled')
    }
}
const errModal = new Modal($errModal, options)
const loadingModal = new Modal($loadingModal, options)
const previewModal = new Modal($previewModal, options)
const previewDocumentModal = new Modal($previewDocumentModal, options)
const previewImageModal = new Modal($previewImageModal, options)
let xhrBalance
let xhrBalanceRemaining
var errAltSubMessageModal = document.getElementById("altSubMessageModal")
var errMessage = document.getElementById("errMessageModal")
var errSubMessage = document.getElementById("errSubMessageModal")
var errListMessage = document.getElementById("err-list")
var errListTitleMessage = document.getElementById("err-list-title")
var procBtn = document.getElementById('submitBtn')
var procTitleMessageModal = document.getElementById("titleMessageModal")
var uploadedFile = []
var uploadDropzone = document.getElementById("dropzoneArea")
var uploadDropzoneAlt = document.getElementById("dropzoneAreaCnv")
var uploadDropzoneSingle = document.getElementById("dropzoneAreaSingle")
var adobeClientID = "STATIC_CLIENT_ID"
var apiUrl = 'http://192.168.0.2'
var bearerToken = "STATIC_BEARER"
var googleViewerUrl = 'https://docs.google.com/viewerng/viewer?url='
var uploadPath = '/storage/upload/'
var uploadStats = false
var xhrScsUploads = 0
var xhrTotalUploads = 0

if (procBtn) {
    remainingBalance().then(function () {
        procBtn.onclick = function(event) {
            if (xhrScsUploads > 0 && xhrTotalUploads > 0) {
                if (xhrScsUploads == xhrTotalUploads) {
                    submit(event)
                } else {
                    event.preventDefault()
                    errMessage.innerText  = "Sorry, we're still uploading your files"
                    errSubMessage.innerText = ""
                    errListTitleMessage.innerText = "Error message"
                    resetErrListMessage()
                    generateMesssage(xhrScsUploads+" of "+xhrTotalUploads+" files are still uploading")
                    errAltSubMessageModal.style = null
                    loadingModal.hide()
                    errModal.show()
                }
            } else {
                event.preventDefault()
                errMessage.innerText  = "PDF file can not be processed !"
                errSubMessage.innerText = ""
                errListTitleMessage.innerText = "Error message"
                resetErrListMessage()
                generateMesssage("No file has been chosen")
                errAltSubMessageModal.style = null
                loadingModal.hide()
                errModal.show()
            }
        }
    }).catch(function (error) {
        errMessage.innerText  = "There was unexpected error !"
        errSubMessage.innerText = ""
        errListTitleMessage.innerText = "Error message"
        resetErrListMessage()
        generateMesssage("Cannot establish connection with the server")
        errAltSubMessageModal.style = null
        loadingModal.hide()
        errModal.show()
        console.log(error)
    })
}

if (uploadDropzone) {
    let uploadDropzone = new Dropzone("#dropzoneArea", {
        url: apiUrl+"/api/v1/file/upload",
        paramName: "file",
        maxFilesize: 25,
        maxFiles: 5,
        acceptedFiles: "application/pdf",
        addRemoveLinks: true,
        dictDefaultMessage: "",
        dictRemoveFile: "Remove",
        timeout: 600000,
        previewTemplate: '<div class="dz-file-preview dz-preview dz-processing dz-success dz-complete z-0">' +
                            '<div class="flex flex-col items-center justify-center">' +
                                '<div class="mt-2 flex items-center justify-center lg:h-[200px] lg:w-[150px]">'+
                                    '<img id="imgThumbnail" class="dz-image-thumbnail h-48 w-32 object-scale-down" src="/assets/icons/placeholder_pdf.svg">' +
                                '</div>' +
                                '<div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>' +
                                '<div class="dz-success-mark"><svg class="w-4 h-4 text-ac" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.5 11.5 11 14l4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg></div>' +
                                '<div class="dz-error-mark"><svg class="w-4 h-4 text-rt1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"></path></svg></div>' +
                                '<div class="dz-details -mt-8">' +
                                    '<div class="dz-filename font-sm font-magistral text-lt1"><span data-dz-name></span></div>' +
                                '</div>' +
                                '<div class="dz-error-message mt-2 ms-1 lg:ms-4"><span data-dz-errormessage></span></div>' +
                                '<div class="flex flex-row mx-auto">'+
                                    '<button type="button" id="prvBtn" class="prvBtn mt-2 mx-4 p-2 bg-pc2 text-lt rounded-lg cursor-pointer w-8 h-8 text-center flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-white"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg></button>' +
                                    '<button type="button" id="rmvBtn" class="rmvBtn mt-2 mx-4 p-2 bg-rt1 text-lt rounded-lg cursor-pointer w-8 h-8 text-center flex items-center justify-center" data-dz-remove=""><svg class="w-6 h-6 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z"></path></svg></button>' +
                                '</div>' +
                            '</div>' +
                        '</div>',
        clickable: true,
        headers: {
            "Authorization": "Bearer "+import.meta.env.VITE_JWT_TOKEN
        },
        init: function () {
            document.getElementById("dropzoneUploadInit").addEventListener("click", function () {
                uploadDropzone.hiddenFileInput.click()
            })

            document.getElementById("dropzoneUploadExt").addEventListener("click", function () {
                if (uploadDropzone.files.length >= 4) {
                    document.getElementById("dropzoneUiExt").classList.add('hidden')
                } else {
                    uploadDropzone.hiddenFileInput.click()
                    document.getElementById("dropzoneUiExt").classList.remove('hidden')
                }
            })

            this.on("addedfile", function (file) {
                xhrTotalUploads = uploadDropzone.files.length

                var rmvLink = document.getElementsByClassName("dz-remove")
                var dzFileLayout = document.querySelectorAll('[data-dz-name=""]')

                document.querySelector('.dz-default.dz-message').style.display = "none"
                document.getElementById("dropzoneUiInit").style.display = "none"

                for (var i = 0; i < rmvLink.length; i++) {
                    rmvLink[i].style.display = "none"
                }

                dzFileLayout.forEach(function(element) {
                    element.style.borderColor = 'transparent'
                    element.style.backgroundColor = 'transparent'
                })

                if (file.type === "application/pdf") {
                    generatePdfThumbnail(file)

                    if (uploadDropzone.files.length >= 4) {
                        document.getElementById("dropzoneUiExt").classList.add('hidden')
                        uploadDropzone.hiddenFileInput.setAttribute("disabled", "disabled")
                    } else {
                        document.getElementById("dropzoneUiExt").classList.remove('hidden')
                        uploadDropzone.hiddenFileInput.removeAttribute("disabled", "disabled")
                    }

                    procBtn.style.backgroundColor="#4DAAAA"
                    procBtn.style.borderColor = "transparent"

                    var prvBtn = document.querySelectorAll(".prvBtn")
                    var rmvBtn = document.querySelectorAll(".rmvBtn")
                    prvBtn.forEach(function(button) {
                        button.addEventListener("click", function(event) {
                            var parentContainer = event.target.closest('.dz-file-preview')
                            var filenameElement = parentContainer.querySelector('.dz-filename span')
                            var uploadedFile1 = fileNameFormat(filenameElement.innerText)
                            var newUrl = apiUrl+uploadPath+uploadedFile1
                            var adobeDCView = new AdobeDC.View(
                                {
                                    clientId: adobeClientID,
                                    divId: "adobe-dc-view"
                                })
                            adobeDCView.previewFile(
                                {
                                    content:{
                                        location:{
                                            url: newUrl
                                        }
                                    },
                                    metaData:{
                                        fileName: uploadedFile1
                                    }
                                },
                                {
                                    embedMode: "SIZED_CONTAINER",
                                    focusOnRendering: true,
                                    showDownloadPDF: false
                                })
                            previewModal.show()
                        })
                    })
                    rmvBtn.forEach(function(button) {
                        button.addEventListener("click", function() {
                            var adobeScript = document.getElementById("adobe-dc-view")
                            if (adobeScript) {
                                adobeScript.innerHTML = '<script src="https://acrobatservices.adobe.com/view-sdk/viewer.js"></script>'
                            }

                            if (uploadDropzone.files.length > 3) {
                                document.getElementById("dropzoneUiExt").classList.add('hidden')
                            } else if (uploadDropzone.files.length > 0 && uploadDropzone.files.length < 5) {
                                uploadDropzone.hiddenFileInput.removeAttribute("disabled", "disabled")
                                document.getElementById("dropzoneUiExt").classList.remove('hidden')
                            }
                        })
                    })
                }
            })

            this.on("removedfile", function (file) {
                if (uploadDropzone.files.length === 0) {
                    document.getElementById("dropzoneUiInit").style.display = null
                    document.getElementById("dropzoneUiExt").classList.add('hidden')
                    procBtn.style.backgroundColor = null
                    procBtn.style.borderColor = '#E0E4E5'
                    procBtn.style.color = null
                }

                if (file) {
                    const filePath = uploadPath + file.name

                    if (xhrScsUploads > 0) {
                        let dzErrorMessage = file.previewElement.querySelector('.dz-error-message')
                        if (dzErrorMessage.textContent == '') {
                            xhrScsUploads = xhrScsUploads - 1
                        }
                        uploadedFile = uploadedFile.filter(item => !file.name.includes(item))
                    }

                    if (xhrTotalUploads > 0) {
                        xhrTotalUploads = xhrTotalUploads - 1
                    }

                    fetch(apiUrl+"/api/v1/file/remove", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "Authorization": `Bearer ${bearerToken}`,
                            "file": filePath,
                        },
                        body: JSON.stringify({
                            file: filePath,
                        }),
                    })
                    .then(
                        response => response.json()
                    )
                    .catch(error => {
                        console.error('Error: Failed to remove file: ', error)
                    })
                } else {
                    console.error('Error: File object is null or undefined.')
                }
            })

            this.on("success", function (response) {
                var uploadedFileName = response.name
                uploadedFile.push(uploadedFileName)

                xhrScsUploads = xhrScsUploads + 1

                if (xhrScsUploads == xhrTotalUploads) {
                    uploadStats = true
                } else {
                    uploadStats = false
                }
            })

            this.on("error", function(file, dropzoneErrMessage, xhr) {
                let dzErrorMessage = file.previewElement.querySelector('.dz-error-message')
                if (dzErrorMessage) {
                    let newErrMessage
                    if (dropzoneErrMessage == '[object Object]') {
                        if (xhr && xhr.readyState == 4) {
                            if (xhr.response) {
                                var xhrReturn = JSON.parse(xhr.responseText)
                                if (xhrReturn.errors !== '') {
                                    newErrMessage = xhrReturn.errors
                                } else {
                                    newErrMessage = "There was an unexpected error!"
                                }
                            } else {
                                newErrMessage = "There was an unexpected error!"
                            }
                        } else {
                            newErrMessage = "There was an unexpected error!"
                        }
                    } else {
                        newErrMessage = dropzoneErrMessage
                    }
                    dzErrorMessage.textContent = newErrMessage
                }
            })

            this.on("timeout", function(file) {
                uploadDropzone.removeFile(file)
                uploadedFile = uploadedFile.filter(item => !file.name.includes(item))
                errMessage.innerText  = "Connection timeout !"
                errSubMessage.innerText = "Please try again later"
                errListTitleMessage.innerText = "Failed to upload:"
                resetErrListMessage()
                generateMesssage(file.name)
                errAltSubMessageModal.style = null
                errModal.show()
            })
        }
    })

    if (!uploadDropzone) {
        if (procBtn) {
            if (document.getElementById('compress') !== null || document.getElementById('cnvFrPDF') !== null
                || document.getElementById('merge') !== null) {
                errMessage.innerText  = "There was unexpected error !"
                errSubMessage.innerText = ""
                errListTitleMessage.innerText = "Error message"
                resetErrListMessage()
                generateMesssage("Cannot establish connection with the server")
                errAltSubMessageModal.style = null
                loadingModal.hide()
                errModal.show()
            }
        }
    }
}

if (uploadDropzoneAlt) {
    let uploadDropzoneAlt = new Dropzone("#dropzoneAreaCnv", {
        url: apiUrl+"/api/v1/file/upload",
        paramName: "file",
        maxFilesize: 25,
        maxFiles: 5,
        acceptedFiles: ".xlsx, .xls, .ppt, .pptx, .docx, doc, image/*",
        addRemoveLinks: true,
        dictDefaultMessage: "",
        dictRemoveFile: "Remove",
        timeout: 60000,
        previewTemplate: '<div class="dz-file-preview dz-preview dz-processing dz-success dz-complete z-0">' +
                            '<div class="flex flex-col items-center justify-center">' +
                                '<div class="mt-2 flex items-center justify-center lg:h-[200px] lg:w-[150px]">'+
                                    '<img id="imgThumbnail" class="dz-image-thumbnail h-48 w-32 object-scale-down" src="/assets/icons/placeholder_pdf.svg">' +
                                '</div>' +
                                '<div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>' +
                                '<div class="dz-success-mark"><svg class="w-4 h-4 text-ac" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.5 11.5 11 14l4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg></div>' +
                                '<div class="dz-error-mark"><svg class="w-4 h-4 text-rt1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"></path></svg></div>' +
                                '<div class="dz-details -mt-8">' +
                                    '<div class="dz-filename font-sm font-magistral text-lt1"><span data-dz-name></span></div>' +
                                '</div>' +
                                '<div class="dz-error-message mt-2 ms-1 lg:ms-4"><span data-dz-errormessage></span></div>' +
                                '<div class="flex flex-row mx-auto">'+
                                    '<button type="button" id="prvBtn" class="prvBtn mt-2 mx-4 p-2 bg-pc2 text-lt rounded-lg cursor-pointer w-8 h-8 text-center flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-white"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg></button>' +
                                    '<button type="button" id="rmvBtn" class="rmvBtn mt-2 mx-4 p-2 bg-rt1 text-lt rounded-lg cursor-pointer w-8 h-8 text-center flex items-center justify-center" data-dz-remove=""><svg class="w-6 h-6 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z"></path></svg></button>' +
                                '</div>' +
                            '</div>' +
                        '</div>',
        clickable: true,
        headers: {
            "Authorization": "Bearer "+bearerToken
        },
        init: function () {
            document.getElementById("dropzoneUploadInit").addEventListener("click", function () {
                uploadDropzoneAlt.hiddenFileInput.click()
            })

            document.getElementById("dropzoneUploadExt").addEventListener("click", function () {
                if (uploadDropzoneAlt.files.length >= 4) {
                    document.getElementById("dropzoneUiExt").classList.add('hidden')
                } else {
                    uploadDropzoneAlt.hiddenFileInput.click()
                    document.getElementById("dropzoneUiExt").classList.remove('hidden')
                }
            })

            this.on("addedfile", function (file) {
                xhrTotalUploads = uploadDropzoneAlt.files.length

                var rmvLink = document.getElementsByClassName("dz-remove")
                var dzFileLayout = document.querySelectorAll('[data-dz-name=""]')

                document.querySelector('.dz-default.dz-message').style.display = "none"
                document.getElementById("dropzoneUiInit").style.display = "none"

                for (var i = 0; i < rmvLink.length; i++) {
                    rmvLink[i].style.display = "none"
                }

                dzFileLayout.forEach(function(element) {
                    element.style.borderColor = 'transparent'
                    element.style.backgroundColor = 'transparent'
                })

                if (uploadDropzoneAlt.files.length >= 4) {
                    document.getElementById("dropzoneUiExt").classList.add('hidden')
                    uploadDropzoneAlt.hiddenFileInput.setAttribute("disabled", "disabled")
                } else {
                    procBtn.style.backgroundColor="#4DAAAA"
                    procBtn.style.borderColor = "transparent"
                    document.getElementById("dropzoneUiExt").classList.remove('hidden')
                    uploadDropzoneAlt.hiddenFileInput.removeAttribute("disabled", "disabled")
                }

                var prvBtn = document.querySelectorAll(".prvBtn")
                var rmvBtn = document.querySelectorAll(".rmvBtn")
                prvBtn.forEach(function(button) {
                    button.addEventListener("click", function(event) {
                        var parentContainer = event.target.closest('.dz-preview')
                        var filenameElement = parentContainer.querySelector('.dz-filename span')
                        var uploadedFile1 = fileNameFormat(filenameElement.innerText)
                        var imageUrl = apiUrl+uploadPath+uploadedFile1
                        var documentUrl = googleViewerUrl+apiUrl+uploadPath+uploadedFile1+'&embedded=true'
                        if (file.type.startsWith('image/')) {
                            document.getElementById('imgPrv').src = imageUrl
                            previewImageModal.show()
                        } else {
                            document.getElementById('iFrame').src = documentUrl
                            previewDocumentModal.show()
                        }
                    })
                })
                rmvBtn.forEach(function(button) {
                    button.addEventListener("click", function() {
                        if (uploadDropzoneAlt.files.length > 3) {
                            document.getElementById("dropzoneUiExt").classList.add('hidden')
                        } else if (uploadDropzoneAlt.files.length > 0 && uploadDropzoneAlt.files.length < 5) {
                            uploadDropzoneAlt.hiddenFileInput.removeAttribute("disabled", "disabled")
                            document.getElementById("dropzoneUiExt").classList.remove('hidden')
                        }
                    })
                })
            })

            this.on("removedfile", function (file) {
                if (uploadDropzoneAlt.files.length === 0) {
                    document.getElementById("dropzoneUiInit").style.display = null
                    document.getElementById("dropzoneUiExt").classList.add('hidden')
                    procBtn.style.backgroundColor = null
                    procBtn.style.borderColor = '#E0E4E5'
                    procBtn.style.color = null
                }

                if (file) {
                    const filePath = uploadPath + file.name

                    if (xhrScsUploads > 0) {
                        let dzErrorMessage = file.previewElement.querySelector('.dz-error-message')
                        if (dzErrorMessage.textContent == '') {
                            xhrScsUploads = xhrScsUploads - 1
                        }
                        uploadedFile = uploadedFile.filter(item => !file.name.includes(item))
                    }

                    if (xhrTotalUploads > 0) {
                        xhrTotalUploads = xhrTotalUploads - 1
                    }

                    fetch(apiUrl+"/api/v1/file/remove", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "Authorization": `Bearer ${bearerToken}`,
                            "file": filePath,
                        },
                        body: JSON.stringify({
                            file: filePath,
                        }),
                    })
                    .then(
                        response => response.json()
                    )
                    .catch(error => {
                        console.error('Error: Failed to remove file: ', error)
                    })
                } else {
                    console.error('Error: File object is null or undefined.')
                }
            })

            this.on("success", function (file, response) {
                var uploadedFileName = response.fileName
                uploadedFile.push(uploadedFileName)

                xhrScsUploads = xhrScsUploads + 1

                if (xhrScsUploads == xhrTotalUploads) {
                    uploadStats = true
                } else {
                    uploadStats = false
                }

                if (!file.type.startsWith('image/')) {
                    generateThumbnail(file.name)
                    .then(function(thumbnailURL) {
                        file.previewElement.querySelector(".dz-image-thumbnail").src = apiUrl+thumbnailURL
                    })
                    .catch(function(error) {
                        file.previewElement.querySelector(".dz-image-thumbnail").src = "/assets/icons/placeholder_pptx.svg"
                    })
                }
            })

            this.on("error", function(file, dropzoneErrMessage, xhr) {
                let dzErrorMessage = file.previewElement.querySelector('.dz-error-message')
                if (dzErrorMessage) {
                    let newErrMessage
                    if (dropzoneErrMessage == '[object Object]') {
                        if (xhr && xhr.readyState == 4) {
                            if (xhr.response) {
                                var xhrReturn = JSON.parse(xhr.responseText)
                                if (xhrReturn.errors !== '') {
                                    newErrMessage = xhrReturn.errors
                                } else {
                                    newErrMessage = "There was an unexpected error!"
                                }
                            } else {
                                newErrMessage = "There was an unexpected error!"
                            }
                        } else {
                            newErrMessage = "There was an unexpected error!"
                        }
                    } else {
                        newErrMessage = dropzoneErrMessage
                    }
                    dzErrorMessage.textContent = newErrMessage
                }
            })

            this.on("thumbnail", function (file) {
                if (file.type.startsWith('image/')) {
                    file.previewElement.querySelector(".dz-image-thumbnail").src = file.dataURL
                }
            })
        }
    })

    if (!uploadDropzoneAlt) {
        if (procBtn && document.getElementById('cnvToPDF') !== null) {
            errMessage.innerText  = "There was unexpected error !"
            errSubMessage.innerText = ""
            errListTitleMessage.innerText = "Error message"
            resetErrListMessage()
            generateMesssage("Cannot establish connection with the server")
            errAltSubMessageModal.style = null
            loadingModal.hide()
            errModal.show()
        }
    }
}

if (uploadDropzoneSingle) {
    let uploadDropzoneSingle = new Dropzone("#dropzoneAreaSingle", {
        url: apiUrl+"/api/v1/file/upload",
        paramName: "file",
        maxFilesize: 25,
        maxFiles: 1,
        acceptedFiles: "application/pdf",
        addRemoveLinks: true,
        dictDefaultMessage: "",
        dictRemoveFile: "Remove",
        tiemout: 60000,
        previewTemplate: '<div class="dz-file-preview dz-preview dz-processing dz-success dz-complete z-0">' +
                            '<div class="flex flex-col items-center justify-center">' +
                                '<div class="mt-2 flex items-center justify-center lg:h-[200px] lg:w-[150px]">'+
                                    '<img id="imgThumbnail" class="dz-image-thumbnail h-48 w-32 object-scale-down" src="/assets/icons/placeholder_pdf.svg">' +
                                '</div>' +
                                '<div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>' +
                                '<div class="dz-success-mark"><svg class="w-4 h-4 text-ac" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.5 11.5 11 14l4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg></div>' +
                                '<div class="dz-error-mark"><svg class="w-4 h-4 text-rt1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"></path></svg></div>' +
                                '<div class="dz-details -mt-8">' +
                                    '<div class="dz-filename font-sm font-magistral text-lt1"><span data-dz-name></span></div>' +
                                '</div>' +
                                '<div class="dz-error-message mt-2 ms-1 lg:ms-4"><span data-dz-errormessage></span></div>' +
                                '<div class="flex flex-row mx-auto">'+
                                    '<button type="button" id="prvBtn" class="prvBtn mt-2 mx-4 p-2 bg-pc2 text-lt rounded-lg cursor-pointer w-8 h-8 text-center flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-white"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg></button>' +
                                    '<button type="button" id="rmvBtn" class="rmvBtn mt-2 mx-4 p-2 bg-rt1 text-lt rounded-lg cursor-pointer w-8 h-8 text-center flex items-center justify-center" data-dz-remove=""><svg class="w-6 h-6 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z"></path></svg></button>' +
                                '</div>' +
                            '</div>' +
                        '</div>',
        clickable: true,
        headers: {
            "Authorization": "Bearer "+bearerToken
        },
        init: function () {
            document.getElementById("dropzoneUploadInit").addEventListener("click", function () {
                uploadDropzoneSingle.hiddenFileInput.click()
            })

            document.getElementById("dropzoneUploadExt").addEventListener("click", function () {
                if (uploadDropzoneSingle.files.length >= 1) {
                    document.getElementById("dropzoneUiExt").classList.add('hidden')
                } else {
                    uploadDropzoneSingle.hiddenFileInput.click()
                    document.getElementById("dropzoneUiExt").classList.remove('hidden')
                }
            })

            this.on("addedfile", function (file) {
                xhrTotalUploads = uploadDropzoneSingle.files.length

                var rmvLink = document.getElementsByClassName("dz-remove")
                var dzFileLayout = document.querySelectorAll('[data-dz-name=""]')

                document.querySelector('.dz-default.dz-message').style.display = "none"
                document.getElementById("dropzoneUiInit").style.display = "none"

                for (var i = 0; i < rmvLink.length; i++) {
                    rmvLink[i].style.display = "none"
                }

                dzFileLayout.forEach(function(element) {
                    element.style.borderColor = 'transparent'
                    element.style.backgroundColor = 'transparent'
                })

                if (file.type === "application/pdf") {
                    generatePdfThumbnail(file)

                    if (uploadDropzoneSingle.files.length >= 1) {
                        document.getElementById("dropzoneUiExt").classList.add('hidden')
                        uploadDropzoneSingle.hiddenFileInput.setAttribute("disabled", "disabled")
                    } else {
                        document.getElementById("dropzoneUiExt").classList.remove('hidden')
                        uploadDropzoneSingle.hiddenFileInput.removeAttribute("disabled", "disabled")
                    }

                    procBtn.style.backgroundColor="#4DAAAA"
                    procBtn.style.color = "#E0E4E5"

                    var prvBtn = document.querySelectorAll(".prvBtn")
                    var rmvBtn = document.querySelectorAll(".rmvBtn")
                    prvBtn.forEach(function(button) {
                        button.addEventListener("click", function(event) {
                            var parentContainer = event.target.closest('.dz-file-preview')
                            var filenameElement = parentContainer.querySelector('.dz-filename span')
                            var uploadedFile1 = fileNameFormat(filenameElement.innerText)
                            var newUrl = apiUrl+uploadPath+uploadedFile1
                            var adobeDCView = new AdobeDC.View(
                                {
                                    clientId: adobeClientID,
                                    divId: "adobe-dc-view"
                                })
                            adobeDCView.previewFile(
                                {
                                    content:{
                                        location:{
                                            url: newUrl
                                        }
                                    },
                                    metaData:{
                                        fileName: uploadedFile1
                                    }
                                },
                                {
                                    embedMode: "SIZED_CONTAINER",
                                    focusOnRendering: true,
                                    showDownloadPDF: false
                                })
                            previewModal.show()
                        })
                    })
                    rmvBtn.forEach(function(button) {
                        button.addEventListener("click", function() {
                            var adobeScript = document.getElementById("adobe-dc-view")
                            if (adobeScript) {
                                adobeScript.innerHTML = '<script src="https://acrobatservices.adobe.com/view-sdk/viewer.js"></script>'
                            }

                            if (uploadDropzoneSingle.files.length >= 1) {
                                uploadDropzoneSingle.hiddenFileInput.removeAttribute("disabled", "disabled")
                                document.getElementById("dropzoneUiExt").classList.remove('hidden')
                            } else {
                                document.getElementById("dropzoneUiExt").classList.add('hidden')
                            }
                        })
                    })
                }
            })

            this.on("removedfile", function (file) {
                if (uploadDropzoneSingle.files.length === 0) {
                    document.getElementById("dropzoneUiInit").style.display = null
                    document.getElementById("dropzoneUiExt").classList.add('hidden')
                    procBtn.style.backgroundColor = null
                    procBtn.style.color = null
                }

                if (file) {
                    const filePath = uploadPath+file.name

                    if (xhrScsUploads > 0) {
                        let dzErrorMessage = file.previewElement.querySelector('.dz-error-message')
                        if (dzErrorMessage.textContent == '') {
                            xhrScsUploads = xhrScsUploads - 1
                        }
                        uploadedFile = uploadedFile.filter(item => !file.name.includes(item))
                    }

                    if (xhrTotalUploads > 0) {
                        xhrTotalUploads = xhrTotalUploads - 1
                    }

                    fetch(apiUrl+"/api/v1/file/remove", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "Authorization": `Bearer ${bearerToken}`,
                            "file": filePath,
                        },
                        body: JSON.stringify({
                            file: filePath,
                        }),
                    })
                    .then(
                        response => response.json()
                    )
                    .catch(error => {
                        console.error('Error: Failed to remove file: ', error)
                    })
                } else {
                    console.error('Error: File object is null or undefined.')
                }
            })

            this.on("success", function (response) {
                var uploadedFileName = response.name
                uploadedFile.push(uploadedFileName)

                xhrScsUploads = xhrScsUploads + 1

                if (xhrScsUploads == xhrTotalUploads) {
                    uploadStats = true
                } else {
                    uploadStats = false
                }
            })

            this.on("error", function(file, dropzoneErrMessage, xhr) {
                let dzErrorMessage = file.previewElement.querySelector('.dz-error-message')
                if (dzErrorMessage) {
                    let newErrMessage
                    if (dropzoneErrMessage == '[object Object]') {
                        if (xhr && xhr.readyState == 4) {
                            if (xhr.response) {
                                var xhrReturn = JSON.parse(xhr.responseText)
                                if (xhrReturn.errors !== '') {
                                    newErrMessage = xhrReturn.errors
                                } else {
                                    newErrMessage = "There was an unexpected error!"
                                }
                            } else {
                                newErrMessage = "There was an unexpected error!"
                            }
                        } else {
                            newErrMessage = "There was an unexpected error!"
                        }
                    } else {
                        newErrMessage = dropzoneErrMessage
                    }
                    dzErrorMessage.textContent = newErrMessage
                }
            })

            this.on("timeout", function(file) {
                uploadDropzone.removeFile(file)
                uploadedFile = uploadedFile.filter(item => !file.name.includes(item))
                errMessage.innerText  = "Connection timeout !"
                errSubMessage.innerText = "Please try again later"
                errListTitleMessage.innerText = "Failed to upload:"
                resetErrListMessage()
                generateMesssage(file.name)
                errAltSubMessageModal.style = null
                errModal.show()
            })
        }
    })

    if (!uploadDropzoneSingle) {
        if (procBtn) {
            if (document.getElementById('split') !== null || document.getElementById('watermark') !== null) {
                errMessage.innerText  = "There was unexpected error !"
                errSubMessage.innerText = ""
                errListTitleMessage.innerText = "Error message"
                resetErrListMessage()
                generateMesssage("Cannot establish connection with the server")
                errAltSubMessageModal.style = null
                loadingModal.hide()
                errModal.show()
            }
        }
    }
}

function generatePdfThumbnail(file) {
    const fileReader = new FileReader()
    fileReader.onload = function () {
      const typedArray = new Uint8Array(this.result)
      pdfjsLib.getDocument(typedArray).promise.then(function (pdf) {
        pdf.getPage(1).then(function (page) {
          const canvas = document.createElement("canvas")
          const ctx = canvas.getContext("2d")
          const viewport = page.getViewport({ scale: 0.5 })
          canvas.width = viewport.width
          canvas.height = viewport.height

          const renderContext = {
            canvasContext: ctx,
            viewport: viewport,
          }

          page.render(renderContext).promise.then(function () {
            const thumbnail = canvas.toDataURL("image/jpeg")
            const previewElement = file.previewElement
            const dzImage = previewElement.querySelector(".dz-image-thumbnail")
            dzImage.src = thumbnail
          })
        })
      })
    }
    fileReader.readAsArrayBuffer(file)
}

function getUploadedFileName() {
    return uploadedFile
}

function apiGateway(proc, action) {
    var files = getUploadedFileName()
    sendToAPI(files,proc,action).then(function () {
        loadingModal.hide()
    }).catch(function (error) {
        loadingModal.hide()
        console.error(error)
    })
}

function generateThumbnail(fileName) {
    return new Promise(function (resolve, reject) {
        var xhr = new XMLHttpRequest()
        var formData = new FormData()
        formData.append('file', fileName)
        xhr.open('POST', apiUrl+'/api/v1/file/thumbnail', true)
        xhr.setRequestHeader('Authorization', 'Bearer ' + bearerToken)
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    var xhrReturn = JSON.parse(xhr.responseText)
                    if (xhrReturn.status == 200) {
                        resolve(xhrReturn.files)
                    } else {
                        reject(new Error('API response error: ' + xhrReturn.message))
                    }
                } else {
                    reject(new Error('API response error! Status: ' + xhr.status))
                }
            }
        }
        xhr.send(formData)
    })
}

function sendToAPI(files, proc, action) {
    return new Promise(function (resolve, reject) {
        var xhr = new XMLHttpRequest()
        var formData = new FormData()
        if (proc == 'compress') {
            var compMethodValue = document.querySelector('input[name="compMethod"]:checked').value
            formData.append('compMethod', compMethodValue)
        } else if (proc == 'convert') {
            if (document.getElementById("cnvToPDF") !== null) {
                var cnvValue = "pdf"
                formData.append('extImage', false.toString())
            } else if (document.getElementById("cnvFrPDF") !== null) {
                var cnvValue = document.querySelector('input[name="convertType"]:checked').value
                var imgValue = document.getElementById("extImage")
                if (imgValue.checked) {
                    formData.append('extImage', true.toString())
                } else {
                    formData.append('extImage', false.toString())
                }
            }
            formData.append('convertType', cnvValue)
        } else if (proc == 'split') {
            let mergePdf
            var customPageSplit = document.getElementById('customPageSplit').value
            var customPageDelete = document.getElementById('customPageDelete').value
            var firstPage = document.getElementById('fromPage').value
            var lastPage = document.getElementById('toPage').value
            if (firstPage && lastPage) {
                mergePdf = document.getElementById('mergePDF').checked
            } else {
                mergePdf = document.getElementById('mergePDF1').checked
            }
            formData.append('action', action)
            formData.append('fromPage', firstPage)
            formData.append('toPage', lastPage)
            formData.append('mergePDF', mergePdf.toString())
            formData.append('customPageSplit', customPageSplit)
            formData.append('customPageDelete', customPageDelete)
        } else if (proc == 'watermark') {
            if (document.getElementById('firstRadio').checked == true) {
                let wmLayoutStyle
                let wmRotation
                var imgFile = document.getElementById('wm_file_input').files[0]
                var wmPage = document.getElementById('watermarkPageImage').value
                var wmTransparency = document.getElementById('watermarkImageTransparency').value
                var wmMosaic = document.getElementById('isMosaicImage').checked
                if (document.getElementById('wmRadioImageLayoutStyleA').checked == true) {
                    wmLayoutStyle = document.getElementById('wmRadioImageLayoutStyleA').value
                } else if (document.getElementById('wmRadioImageLayoutStyleB').checked == true) {
                    wmLayoutStyle = document.getElementById('wmRadioImageLayoutStyleB').value
                } else {
                    wmLayoutStyle = document.getElementById('wmRadioImageLayoutStyleA').value
                }
                if (document.getElementById('wmRadioImageRotationA').checked == true) {
                    wmRotation = document.getElementById('wmRadioImageRotationA').value
                } else if (document.getElementById('wmRadioImageRotationB').checked == true) {
                    wmRotation = document.getElementById('wmRadioImageRotationB').value
                } else if (document.getElementById('wmRadioImageRotationC').checked == true) {
                    wmRotation = document.getElementById('wmRadioImageRotationC').value
                } else if (document.getElementById('wmRadioImageRotationD').checked == true) {
                    wmRotation = document.getElementById('wmRadioImageRotationD').value
                } else {
                    wmRotation = document.getElementById('wmRadioImageRotationA').value
                }
                formData.append('action', action)
                formData.append('imgFile', imgFile)
                formData.append('wmFontColor', '')
                formData.append('wmFontSize', '')
                formData.append('wmFontStyle', 'Regular')
                formData.append('wmFontFamily', 'Arial')
                formData.append('wmLayoutStyle', wmLayoutStyle)
                formData.append('wmRotation', wmRotation)
                formData.append('wmPage', wmPage)
                formData.append('wmText', '')
                formData.append('wmTransparency', wmTransparency)
                formData.append('wmMosaic', wmMosaic.toString())
            } else if (document.getElementById('secondRadio').checked == true) {
                let wmFontFamily
                let wmFontStyle
                let wmLayoutStyle
                let wmRotation
                var wmFontSize = document.getElementById('watermarkFontSize').value
                var wmFontColor = document.getElementById('watermarkFontColor').value
                var wmPage = document.getElementById('watermarkPageText').value
                var wmText = document.getElementById('watermarkText').value
                var wmTransparency = document.getElementById('watermarkTextTransparency').value
                var wmMosaic = document.getElementById('isMosaicText').checked
                if (document.getElementById('wmRadioFontFamilyA').checked == true) {
                    wmFontFamily = document.getElementById('wmRadioFontFamilyA').value
                } else if (document.getElementById('wmRadioFontFamilyB').checked == true) {
                    wmFontFamily = document.getElementById('wmRadioFontFamilyB').value
                } else if (document.getElementById('wmRadioFontFamilyC').checked == true) {
                    wmFontFamily = document.getElementById('wmRadioFontFamilyC').value
                } else if (document.getElementById('wmRadioFontFamilyD').checked == true) {
                    wmFontFamily = document.getElementById('wmRadioFontFamilyD').value
                } else if (document.getElementById('wmRadioFontFamilyE').checked == true) {
                    wmFontFamily = document.getElementById('wmRadioFontFamilyE').value
                } else if (document.getElementById('wmRadioFontFamilyF').checked == true) {
                    wmFontFamily = document.getElementById('wmRadioFontFamilyF').value
                } else {
                    wmFontFamily = document.getElementById('wmRadioFontFamilyA').value
                }
                if (document.getElementById('wmRadioFontStyleA').checked == true) {
                    wmFontStyle = document.getElementById('wmRadioFontStyleA').value
                } else if (document.getElementById('wmRadioFontStyleB').checked == true) {
                    wmFontStyle = document.getElementById('wmRadioFontStyleB').value
                } else if (document.getElementById('wmRadioFontStyleC').checked == true) {
                    wmFontStyle = document.getElementById('wmRadioFontStyleC').value
                } else {
                    wmFontStyle = document.getElementById('wmRadioFontStyleA').value
                }
                if (document.getElementById('wmRadioLayoutStyleA').checked == true) {
                    wmLayoutStyle = document.getElementById('wmRadioLayoutStyleA').value
                } else if (document.getElementById('wmRadioLayoutStyleB').checked == true) {
                    wmLayoutStyle = document.getElementById('wmRadioLayoutStyleB').value
                } else {
                    wmLayoutStyle = document.getElementById('wmRadioLayoutStyleA').value
                }
                if (document.getElementById('wmRadioRotationA').checked == true) {
                    wmRotation = document.getElementById('wmRadioRotationA').value
                } else if (document.getElementById('wmRadioRotationB').checked == true) {
                    wmRotation = document.getElementById('wmRadioRotationB').value
                } else if (document.getElementById('wmRadioRotationC').checked == true) {
                    wmRotation = document.getElementById('wmRadioRotationC').value
                } else if (document.getElementById('wmRadioRotationD').checked == true) {
                    wmRotation = document.getElementById('wmRadioRotationD').value
                } else {
                    wmRotation = document.getElementById('wmRadioRotationA').value
                }
                formData.append('action', action)
                formData.append('imgFile', '')
                formData.append('wmFontColor', wmFontColor)
                formData.append('wmFontSize', wmFontSize)
                formData.append('wmFontStyle', wmFontStyle)
                formData.append('wmFontFamily', wmFontFamily)
                formData.append('wmLayoutStyle', wmLayoutStyle)
                formData.append('wmRotation', wmRotation)
                formData.append('wmPage', wmPage)
                formData.append('wmText', wmText)
                formData.append('wmTransparency', wmTransparency)
                formData.append('wmMosaic', wmMosaic.toString())
            }
        } else if (proc == 'html') {
            var urlValue = document.getElementById('urlToPDF').value
            formData.append('urlToPDF', urlValue)
        }
        if (proc !== 'html') {
            if (files.length > 1) {
                formData.append('batch', true.toString())
            } else {
                formData.append('batch', false.toString())
            }
            files.forEach(function (file, index) {
                formData.append('file[' + index + ']', file)
            })
        }
        xhr.open('POST', apiUrl+'/api/v1/core/'+proc, true)
        xhr.setRequestHeader('Authorization', 'Bearer ' + bearerToken)
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4) {
                if (xhr.responseText.trim().startsWith('{')) {
                    var xhrReturn = JSON.parse(xhr.responseText)
                    if (xhr.status == 200) {
                        if (xhrReturn.status == 200) {
                            if (xhrReturn.errors == null) {
                                if (proc == 'compress' && xhrTotalUploads == 1)
                                {
                                    document.getElementById("alert-scs").classList.remove("hidden","opacity-0")
                                    document.getElementById("alert-err").classList.add("hidden","opacity-0")
                                    document.getElementById("scsMsgTitle").innerText = "HANA PDF Process completed !"
                                    document.getElementById("scsMsgResult").innerHTML = `
                                        PDF has been compressed to <b>${xhrReturn.newFileSize}</b> with <b>${xhrReturn.compMethod}</b> compression level.
                                    `
                                    document.getElementById("scsMsgLink").href = apiUrl+xhrReturn.fileSource
                                    document.getElementById("scsMsgLink").innerText = "Download PDF"
                                } else {
                                    document.getElementById("alert-scs").classList.remove("hidden","opacity-0")
                                    document.getElementById("alert-err").classList.add("hidden","opacity-0")
                                    document.getElementById("scsMsgTitle").innerText = `HANA PDF Process completed !`
                                    document.getElementById("scsMsgResult").innerText = `Download the file or PDF below.`
                                    document.getElementById("scsMsgLink").href = apiUrl+xhrReturn.fileSource
                                    document.getElementById("scsMsgLink").innerText = "Download PDF"
                                }
                            } else {
                                document.getElementById("alert-scs").classList.add("hidden","opacity-0")
                                document.getElementById("alert-err").classList.remove("hidden","opacity-0")
                                document.getElementById("errMsgTitle").innerText = "HANA PDF Process failed !"
                                document.getElementById("errMsg").innerText = xhrReturn.message
                                document.getElementById("errProcMain").classList.remove("hidden")
                                document.getElementById("errProcId").innerText = xhrReturn.processId
                            }
                        } else {
                            document.getElementById("alert-scs").classList.add("hidden","opacity-0")
                            document.getElementById("alert-err").classList.remove("hidden","opacity-0")
                            document.getElementById("errMsgTitle").innerText = "HANA PDF Process failed !"
                            document.getElementById("errMsg").innerText = xhrReturn.message
                            document.getElementById("errProcMain").classList.remove("hidden")
                            document.getElementById("errProcId").innerText = xhrReturn.processId
                        }
                        resolve()
                    } else {
                        document.getElementById("alert-scs").classList.add("hidden","opacity-0")
                        document.getElementById("alert-err").classList.remove("hidden","opacity-0")
                        document.getElementById("errMsgTitle").innerText = "HANA PDF Process failed !"
                        document.getElementById("errMsg").innerText = xhrReturn.message
                        document.getElementById("errProcMain").classList.remove("hidden")
                        document.getElementById("errProcId").innerText = xhrReturn.processId
                    }
                }
            } else {
                document.getElementById("alert-scs").classList.add("hidden","opacity-0")
                document.getElementById("alert-err").classList.remove("hidden","opacity-0")
                document.getElementById("errMsgTitle").innerText = "HANA PDF Process failed !"
                document.getElementById("errMsg").innerText = "There was unexpected error !, please try again later."
                document.getElementById("errProcMain").classList.add("hidden")
                reject(new Error('API response error !'))
            }
        }
        xhr.send(formData)
    })
}

function submit(event) {
    if (document.getElementById('compress') !== null || document.getElementById('cnvFrPDF') !== null) {
        if (!document.getElementById('firstRadio').checked && !document.getElementById('secondRadio').checked && !document.getElementById('thirdRadio').checked) {
            if (document.getElementById('cnvFrPDF') !== null) {
                if (!document.getElementById('fourthRadio').checked) {
                    var cnvImage = document.getElementById('firstRadio')
                    var cnvXls = document.getElementById('secondRadio')
                    var cnvPptx = document.getElementById('thirdRadio')
                    var cnvdocx = document.getElementById('fourthRadio')
                    event.preventDefault()
                    errMessage.innerText  = "Please select out these fields!"
                    errSubMessage.innerText = ""
                    errListTitleMessage.innerText = "Required fields:"
                    errAltSubMessageModal.style = null
                    resetErrListMessage()
                    generateMesssage("Document Format")
                    cnvImage.style.borderColor = '#A84E4E'
                    cnvXls.style.borderColor = '#A84E4E'
                    cnvPptx.style.borderColor = '#A84E4E'
                    cnvdocx.style.borderColor = '#A84E4E'
                    loadingModal.hide()
                    errModal.show()
                } else {
                    var cnvImage = document.getElementById('firstRadio')
                    var cnvPptx = document.getElementById('secondRadio')
                    var cnvXls = document.getElementById('thirdRadio')
                    var cnvDocx = document.getElementById('fourthRadio')
                    cnvImage.style.borderColor = '#4DAAAA'
                    cnvPptx.style.borderColor = '#4DAAAA'
                    cnvXls.style.borderColor = '#4DAAAA'
                    cnvDocx.style.borderColor = '#4DAAAA'
                    if (getUploadedFileName().length > 0) {
                        if (xhrBalance && xhrBalanceRemaining > 0) {
                             procTitleMessageModal.innerText = "Processing PDF..."
                             errMessage.style.visibility = null
                             errSubMessage.style.visibility = null
                             errAltSubMessageModal.style.display = "none"
                             errModal.hide()
                             loadingModal.show()
                             if (document.getElementById('cnvFrPDF') !== null) {
                                 apiGateway("convert","")
                             } else {
                                 apiGateway("compress","")
                             }
                         } else {
                             event.preventDefault()
                             errMessage.innerText  = "PDF file can not be processed !"
                             errSubMessage.innerText = ""
                             errListTitleMessage.innerText = "Error message"
                             resetErrListMessage()
                             generateMesssage("Remaining monthly limit ("+xhrBalanceRemaining+" out of 250)")
                             errAltSubMessageModal.style = null
                             loadingModal.hide()
                             errModal.show()
                         }
                     } else {
                         event.preventDefault()
                         errMessage.innerText  = "PDF file can not be processed !"
                         errSubMessage.innerText = ""
                         errListTitleMessage.innerText = "Error message"
                         resetErrListMessage()
                         generateMesssage("No file has been chosen")
                         errAltSubMessageModal.style = null
                         loadingModal.hide()
                         errModal.show()
                     }
                }
            } else {
                var compLow = document.getElementById('firstRadio')
                var compMed = document.getElementById('secondRadio')
                var compHigh = document.getElementById('thirdRadio')
                event.preventDefault()
                errMessage.innerText  = "Please select out these fields!"
                errSubMessage.innerText = ""
                errListTitleMessage.innerText = "Required fields:"
                errAltSubMessageModal.style = null
                resetErrListMessage()
                generateMesssage("Compression Quality")
                compLow.style.borderColor = '#A84E4E'
                compMed.style.borderColor = '#A84E4E'
                compHigh.style.borderColor = '#A84E4E'
                loadingModal.hide()
                errModal.show()
            }
        } else {
            var compLow = document.getElementById('firstRadio')
            var compMed = document.getElementById('secondRadio')
            var compHigh = document.getElementById('thirdRadio')
            compLow.style.borderColor = '#4DAAAA'
            compMed.style.borderColor = '#4DAAAA'
            compHigh.style.borderColor = '#4DAAAA'
            if (document.getElementById('cnvFrPDF') !== null) {
                var cnvdocx = document.getElementById('fourthRadio')
                cnvdocx.style.borderColor = '#4DAAAA'
            }
            if (xhrBalance && xhrBalanceRemaining > 0) {
                procTitleMessageModal.innerText = "Processing PDF..."
                errMessage.style.visibility = null
                errSubMessage.style.visibility = null
                errAltSubMessageModal.style.display = "none"
                errModal.hide()
                loadingModal.show()
                if (document.getElementById('cnvFrPDF') !== null) {
                    apiGateway("convert","")
                } else {
                    apiGateway("compress","")
                }
            } else {
                event.preventDefault()
                errMessage.innerText  = "PDF file can not be processed !"
                errSubMessage.innerText = ""
                errListTitleMessage.innerText = "Error message"
                resetErrListMessage()
                generateMesssage("Remaining monthly limit ("+xhrBalanceRemaining+" out of 250)")
                errAltSubMessageModal.style = null
                loadingModal.hide()
                errModal.show()
            }
        }
    } else if (document.getElementById('cnvToPDF') !== null || document.getElementById('merge') !== null) {
        if (xhrBalance && xhrBalanceRemaining > 0) {
            procTitleMessageModal.innerText = "Processing PDF..."
            errMessage.style.visibility = null
            errSubMessage.style.visibility = null
            errAltSubMessageModal.style.display = "none"
            errModal.hide()
            loadingModal.show()
            if (document.getElementById('cnvToPDF') !== null) {
                apiGateway("convert","")
            } else {
                if (getUploadedFileName().length < 2) {
                    event.preventDefault()
                    errMessage.innerText  = "PDF file can not be processed !"
                    errSubMessage.innerText = ""
                    errListTitleMessage.innerText = "Required fields:"
                    errAltSubMessageModal.style = null
                    resetErrListMessage()
                    generateMesssage("Minimum PDF to merge is 2 (Total files: "+getUploadedFileName().length+")")
                    loadingModal.hide()
                    errModal.show()
                } else {
                    apiGateway("merge","")
                }
            }
        } else {
            event.preventDefault()
            errMessage.innerText  = "Kaori"
            errSubMessage.innerText = ""
            errSubMessage.style.visibility = null
            errAltSubMessageModal.style.display = "none"
            loadingModal.hide()
            errModal.show()
        }
    } else if (document.getElementById('splitLayout1')) {
        if (document.getElementById("firstRadio").checked) {
            let cusPage = false
            let fromPage = false
            let toPage = false
            let totalPage
            var customPage = document.getElementById('customPageSplit')
            var firstPage = document.getElementById('fromPage')
            var lastPage = document.getElementById('toPage')
            if (document.getElementById("firstRadio").value == "split") {
                if (document.getElementById("splitRadio")) {
                   if (document.getElementById("thirdRadio").checked) {
                        if (document.getElementById("thirdRadio").value == "selPages") {
                            if (document.getElementById("fromPage").value) {
                                fromPage = true
                            } else {
                                fromPage = false
                            }
                            if (document.getElementById("toPage").value) {
                                toPage = true
                            } else {
                                toPage = false
                            }
                            getTotalPages(apiUrl+uploadPath+getUploadedFileName()[0].replace(/\s/g, '_'))
                            .then((totalPages) => {
                                if (totalPages == null) {
                                    totalPage = null
                                    event.preventDefault()
                                    errMessage.innerText  = "There was unexpected error !"
                                    errSubMessage.innerText = "Please try again later"
                                    errListTitleMessage.innerText = ""
                                    resetErrListMessage()
                                    loadingModal.hide()
                                    errModal.show()
                                } else {
                                    totalPage = totalPages
                                }
                            })
                            .catch((error) => {
                                totalPage = null
                                console.error('Error loading PDF:', error)
                            })
                            if (fromPage && toPage) {
                                if (document.getElementById("fromPage").value.charAt(0) == "-") {
                                    event.preventDefault()
                                    errMessage.innerText  = "Invalid page number range!"
                                    errListTitleMessage.innerText = "Error message"
                                    errAltSubMessageModal.style = null
                                    resetErrListMessage()
                                    generateMesssage("Page number can not use negative number")
                                    firstPage.style.borderColor = '#A84E4E'
                                    loadingModal.hide()
                                    errModal.show()
                                } else if (document.getElementById("toPage").value.charAt(0) == "-") {
                                    event.preventDefault()
                                    errMessage.innerText  = "Invalid page number range!"
                                    errListTitleMessage.innerText = "Error message"
                                    errAltSubMessageModal.style = null
                                    resetErrListMessage()
                                    generateMesssage("Page number can not use negative number")
                                    lastPage.style.borderColor = '#A84E4E'
                                    loadingModal.hide()
                                    errModal.show()
                                } else if (parseInt(document.getElementById("fromPage").value) >= parseInt(document.getElementById("toPage").value)) {
                                    event.preventDefault()
                                    errMessage.innerText  = "Invalid page number range!"
                                    errListTitleMessage.innerText = "Error message"
                                    errAltSubMessageModal.style = null
                                    resetErrListMessage()
                                    generateMesssage("First page can not be more than last page")
                                    generateMesssage("First page can not have same value with last page")
                                    firstPage.style.borderColor = '#A84E4E'
                                    loadingModal.hide()
                                    errModal.show()
                                } else {
                                    procTitleMessageModal.innerText = "Processing PDF..."
                                    errMessage.style.visibility = null
                                    errSubMessage.style.visibility = null
                                    errAltSubMessageModal.style.display = "none"
                                    errModal.hide()
                                    loadingModal.show()
                                    if (xhrBalance && xhrBalanceRemaining > 0) {
                                        apiGateway("split", "split")
                                     } else {
                                         event.preventDefault()
                                         errMessage.innerText  = "PDF file can not be processed !"
                                         errSubMessage.innerText = ""
                                         errListTitleMessage.innerText = "Error message"
                                         resetErrListMessage()
                                         generateMesssage("Remaining monthly limit ("+xhrBalanceRemaining+" out of 250)")
                                         errAltSubMessageModal.style = null
                                         loadingModal.hide()
                                         errModal.show()
                                     }
                                }
                            } else if (!fromPage && !toPage) {
                                event.preventDefault()
                                errMessage.innerText  = "Please fill out these fields!"
                                errSubMessage.innerText = ""
                                errListTitleMessage.innerText = "Required fields:"
                                errAltSubMessageModal.style = null
                                resetErrListMessage()
                                generateMesssage("First Pages")
                                generateMesssage("Last Pages")
                                firstPage.style.borderColor = '#A84E4E'
                                lastPage.style.borderColor = '#A84E4E'
                                loadingModal.hide()
                                errModal.show()
                            } else if (!fromPage && toPage) {
                                event.preventDefault()
                                errMessage.innerText  = "Please fill out these fields!"
                                errSubMessage.innerText = ""
                                errListTitleMessage.innerText = "Required fields:"
                                errAltSubMessageModal.style = null
                                resetErrListMessage()
                                generateMesssage("First Pages")
                                firstPage.style.borderColor = '#A84E4E'
                                loadingModal.hide()
                                errModal.show()
                            } else if (fromPage && !toPage) {
                                event.preventDefault()
                                errMessage.innerText  = "Please fill out these fields!"
                                errSubMessage.innerText = ""
                                errListTitleMessage.innerText = "Required fields:"
                                errAltSubMessageModal.style = null
                                resetErrListMessage()
                                generateMesssage("Last Pages")
                                lastPage.style.borderColor = '#A84E4E'
                                loadingModal.hide()
                                errModal.show()
                            }  else if (parseInt(document.getElementById("fromPage").value) >= totalPage) {
                                event.preventDefault()
                                errMessage.innerText  = "Invalid page number range!"
                                errListTitleMessage.innerText = "Error message"
                                errAltSubMessageModal.style = null
                                resetErrListMessage()
                                generateMesssage("First page can not be more than total page ("+totalPage+")")
                                generateMesssage("First page can not have same value with total page ("+totalPage+")")
                                firstPage.style.borderColor = '#A84E4E'
                                loadingModal.hide()
                                errModal.show()
                            } else if (parseInt(document.getElementById("toPage").value) >= totalPage) {
                                event.preventDefault()
                                errMessage.innerText  = "Invalid page number range!"
                                errListTitleMessage.innerText = "Error message"
                                errAltSubMessageModal.style = null
                                resetErrListMessage()
                                generateMesssage("Last page can not be more than total page ("+totalPage+")")
                                generateMesssage("Last page can not have same value with total page ("+totalPage+")")
                                lastPage.style.borderColor = '#A84E4E'
                                loadingModal.hide()
                                errModal.show()
                            } else {
                                procTitleMessageModal.innerText = "Processing PDF..."
                                errMessage.style.visibility = null
                                errSubMessage.style.visibility = null
                                errAltSubMessageModal.style.display = "none"
                                errModal.hide()
                                loadingModal.show()
                                if (xhrBalance && xhrBalanceRemaining > 0) {
                                    apiGateway("split", "split")
                                 } else {
                                     event.preventDefault()
                                     errMessage.innerText  = "PDF file can not be processed !"
                                     errSubMessage.innerText = ""
                                     errListTitleMessage.innerText = "Error message"
                                     resetErrListMessage()
                                     generateMesssage("Remaining monthly limit ("+xhrBalanceRemaining+" out of 250)")
                                     errAltSubMessageModal.style = null
                                     loadingModal.hide()
                                     errModal.show()
                                 }
                            }
                        } else {
                            event.preventDefault()
                            errMessage.innerText  = "Index out of bound!"
                            errSubMessage.innerText = ""
                            errAltSubMessageModal.style = null
                            errListTitleMessage.innerText = "Error message"
                            resetErrListMessage()
                            generateMesssage("Split selected page logic error")
                            errAltSubMessageModal.style = null
                            loadingModal.hide()
                            errModal.show()
                        }
                    } else if (document.getElementById("fourthRadio").checked) {
                        if (document.getElementById("fourthRadio").value == "cusPages") {
                            if (document.getElementById("customPageSplit").value) {
                                 cusPage = true
                            } else {
                                 cusPage = false
                            }
                            if (cusPage) {
                                procTitleMessageModal.innerText = "Processing PDF..."
                                errMessage.style.visibility = null
                                errSubMessage.style.visibility = null
                                errAltSubMessageModal.style.display = "none"
                                errModal.hide()
                                loadingModal.show()
                                if (xhrBalance && xhrBalanceRemaining > 0) {
                                    apiGateway("split", "split")
                                 } else {
                                     event.preventDefault()
                                     errMessage.innerText  = "PDF file can not be processed !"
                                     errSubMessage.innerText = ""
                                     errListTitleMessage.innerText = "Error message"
                                     resetErrListMessage()
                                     generateMesssage("Remaining monthly limit ("+xhrBalanceRemaining+" out of 250)")
                                     errAltSubMessageModal.style = null
                                     loadingModal.hide()
                                     errModal.show()
                                 }
                            } else {
                                event.preventDefault()
                                errMessage.innerText  = "Please fill out these fields!"
                                errSubMessage.innerText = ""
                                errListTitleMessage.innerText = "Required fields:"
                                errAltSubMessageModal.style = null
                                resetErrListMessage()
                                generateMesssage("Custom Pages")
                                customPage.style.borderColor = '#A84E4E'
                                loadingModal.hide()
                                errModal.show()
                            }
                        } else {
                            event.preventDefault()
                            errMessage.innerText  = "Index out of bound!"
                            errSubMessage.innerText = ""
                            errListTitleMessage.innerText = "Error message"
                            resetErrListMessage()
                            generateMesssage("Split custom page logic error")
                            errAltSubMessageModal.style = null
                            loadingModal.hide()
                            errModal.show()
                        }
                     } else {
                        event.preventDefault()
                        errMessage.innerText  = "Index out of bound!"
                        errSubMessage.innerText = ""
                        errListTitleMessage.innerText = "Error message"
                        resetErrListMessage()
                        generateMesssage("Cannot define selected or custom page")
                        errAltSubMessageModal.style = null
                        loadingModal.hide()
                        errModal.show()
                    }
                } else {
                    event.preventDefault()
                    errMessage.innerText  = "Kaori"
                    errSubMessage.style.visibility = null
                    errAltSubMessageModal.style.display = "none"
                    loadingModal.hide()
                    errModal.show()
                }
            } else {
                event.preventDefault()
                errMessage.innerText  = "Index out of bound!"
                errSubMessage.innerText = ""
                errListTitleMessage.innerinnerTextHTML = "Error message"
                resetErrListMessage()
                generateMesssage("Split options decision logic error")
                errAltSubMessageModal.style = null
                loadingModal.hide()
                errModal.show()
            }
        } else if (document.getElementById("secondRadio").checked) {
            let cusPage = false
            var customPage = document.getElementById('customPageDelete')
            if (document.getElementById("secondRadio").value == "delete") {
                    if (document.getElementById("customPageDelete").value) {
                         cusPage = true
                    } else {
                         cusPage = false
                    }
                    if (cusPage) {
                        procTitleMessageModal.innerText = "Processing PDF..."
                        errMessage.style.visibility = null
                        errSubMessage.style.visibility = null
                        errAltSubMessageModal.style.display = "none"
                        errModal.hide()
                        loadingModal.show()
                        if (xhrBalance && xhrBalanceRemaining > 0) {
                            apiGateway("split", "delete")
                         } else {
                             event.preventDefault()
                             errMessage.innerText  = "PDF file can not be processed !"
                             errSubMessage.innerText = ""
                             errListTitleMessage.innerText = "Error message"
                             resetErrListMessage()
                             generateMesssage("Remaining monthly limit ("+xhrBalanceRemaining+" out of 250)")
                             errAltSubMessageModal.style = null
                             loadingModal.hide()
                             errModal.show()
                         }
                    } else {
                        event.preventDefault()
                        errMessage.innerText  = "Please fill out these fields!"
                        errSubMessage.innerText = ""
                        errListTitleMessage.innerText = "Required fields:"
                        errAltSubMessageModal.style = null
                        resetErrListMessage()
                        generateMesssage("Custom Pages")
                        errSubMessage.style.visibility = null
                        customPage.style.borderColor = '#A84E4E'
                        loadingModal.hide()
                        errModal.show()
                    }
                } else {
                    event.preventDefault()
                    errMessage.innerText  = "Index out of bound!"
                    errSubMessage.innerText = ""
                    errListTitleMessage.innerText = "Error message"
                    resetErrListMessage()
                    generateMesssage("Delete options decision logic error")
                    errAltSubMessageModal.style = null
                    loadingModal.hide()
                    errModal.show()
                }
        } else {
            event.preventDefault()
            errMessage.innerText  = "Index out of bound!"
            errSubMessage.innerText = ""
            errListTitleMessage.innerText = "Error message"
            resetErrListMessage()
            generateMesssage("Split decision logic error")
            errAltSubMessageModal.style = null
            loadingModal.hide()
            errModal.show()
        }
    } else if (document.getElementById('wmMainLayout')) {
        var wmImageSwitcher = document.getElementById("wmTypeImage")
        var wmTextSwitcher = document.getElementById("wmTypeText")
        if (document.getElementById('firstRadio').checked == true) {
            var wmImage = document.getElementById("wm_file_input")
            wmImageSwitcher.checked = true
            wmTextSwitcher.checked = false
            if (document.getElementById("wm_file_input").value) {
                var imgFile = document.getElementById("wm_file_input")
                let fileSize = imgFile.files[0].size
                if (imgFile.files[0].type == "image/jpeg" || imgFile.files[0].type == "image/png"
                    || imgFile.files[0].type == "image/jpg") {
                    if (fileSize >= 5242880) {
                        event.preventDefault()
                        errMessage.innerText  = "Uploaded file has exceeds the limit!"
                        errSubMessage.innerText = ""
                        errListTitleMessage.innerText = "Error message"
                        resetErrListMessage()
                        generateMesssage("Maximum file size 5 MB")
                        errAltSubMessageModal.style = null
                        loadingModal.hide()
                        errModal.show()
                    } else {
                        if (document.getElementById('watermarkPageImage').value) {
                            procTitleMessageModal.innerText = "Processing PDF..."
                            errMessage.style.visibility = null
                            errSubMessage.style.visibility = null
                            errAltSubMessageModal.style.display = "none"
                            errModal.hide()
                            loadingModal.show()
                            if (getUploadedFileName().length > 0) {
                                if (xhrBalance && xhrBalanceRemaining > 0) {
                                    apiGateway("watermark","img")
                                 } else {
                                     event.preventDefault()
                                     errMessage.innerText  = "PDF file can not be processed !"
                                     errSubMessage.innerText = ""
                                     errListTitleMessage.innerText = "Error message"
                                     resetErrListMessage()
                                     generateMesssage("Remaining monthly limit ("+xhrBalanceRemaining+" out of 250)")
                                     errAltSubMessageModal.style = null
                                     loadingModal.hide()
                                     errModal.show()
                                 }
                            } else {
                                event.preventDefault()
                                errMessage.innerText  = "PDF file can not be processed !"
                                errSubMessage.innerText = ""
                                errListTitleMessage.innerText = "Error message"
                                resetErrListMessage()
                                generateMesssage("No file has been chosen")
                                errAltSubMessageModal.style = null
                                loadingModal.hide()
                                errModal.show()
                            }
                        } else {
                            var wmPage = document.getElementById("watermarkPageImage")
                            event.preventDefault()
                            errMessage.innerText  = "Please fill out these fields!"
                            errSubMessage.innerText = ""
                            errListTitleMessage.innerText = "Required fields:"
                            resetErrListMessage()
                            generateMesssage("Pages")
                            errAltSubMessageModal.style = null
                            wmPage.style.borderColor = '#A84E4E'
                            loadingModal.hide()
                            errModal.show()
                        }
                    }
                } else {
                    event.preventDefault()
                    errMessage.innerText  = "Unsupported file format!"
                    errSubMessage.innerText = ""
                    errListTitleMessage.innerText = "Error message"
                    resetErrListMessage()
                    generateMesssage("Supported file format: JPG, PNG")
                    errAltSubMessageModal.style = null
                    loadingModal.hide()
                    errModal.show()
                }
            } else {
                event.preventDefault()
                errMessage.innerText  = "Please fill out these fields!"
                errSubMessage.innerText = ""
                errListTitleMessage.innerText = "Required fields:"
                resetErrListMessage()
                generateMesssage("Image")
                errAltSubMessageModal.style = null
                wmImage.style.borderColor = '#A84E4E'
                loadingModal.hide()
                errModal.show()
            }
        } else if (document.getElementById('secondRadio').checked == true) {
            var wmText = document.getElementById("watermarkText")
            wmImageSwitcher.checked = false
            wmTextSwitcher.checked = true
            if (!document.getElementById('watermarkText').value && !document.getElementById('watermarkPageText').value) {
                var wmPage = document.getElementById("watermarkPageText")
                event.preventDefault()
                errMessage.innerText  = "Please fill out these fields!"
                errSubMessage.innerText = ""
                errListTitleMessage.innerText = "Required fields:"
                resetErrListMessage()
                generateMesssage("Pages")
                generateMesssage("Text")
                errAltSubMessageModal.style = null
                wmText.style.borderColor = '#A84E4E'
                wmPage.style.borderColor = '#A84E4E'
                loadingModal.hide()
                errModal.show()
            } else if (document.getElementById('watermarkText').value) {
                if (document.getElementById('watermarkPageText').value) {
                    procTitleMessageModal.innerText = "Processing PDF..."
                    errMessage.style.visibility = null
                    errSubMessage.style.visibility = null
                    errAltSubMessageModal.style.display = "none"
                    errModal.hide()
                    loadingModal.show()
                    if (xhrBalance && xhrBalanceRemaining > 0) {
                        apiGateway("watermark","txt")
                    } else {
                         event.preventDefault()
                         errMessage.innerText  = "PDF file can not be processed !"
                         errSubMessage.innerText = ""
                         errListTitleMessage.innerText = "Error message"
                         resetErrListMessage()
                         generateMesssage("Remaining monthly limit ("+xhrBalanceRemaining+" out of 250)")
                         errAltSubMessageModal.style = null
                         loadingModal.hide()
                         errModal.show()
                     }
                } else {
                    var wmPage = document.getElementById("watermarkPageText")
                    event.preventDefault()
                    errMessage.innerText  = "Please fill out these fields!"
                    errSubMessage.innerText = ""
                    errListTitleMessage.innerText = "Required fields:"
                    resetErrListMessage()
                    generateMesssage("Pages")
                    errAltSubMessageModal.style = null
                    wmPage.style.borderColor = '#A84E4E'
                    loadingModal.hide()
                    errModal.show()
                }
            } else {
                event.preventDefault()
                errMessage.innerText  = "Please fill out these fields!"
                errSubMessage.innerText = ""
                errListTitleMessage.innerText = "Required fields:"
                resetErrListMessage()
                generateMesssage("Text")
                errAltSubMessageModal.style = null
                wmText.style.borderColor = '#A84E4E'
                loadingModal.hide()
                errModal.show()
            }
        } else {
            event.preventDefault()
            errMessage.innerText  = "Please choose watermark options!"
            errSubMessage.innerText = ""
            errSubMessage.style.visibility = null
            errAltSubMessageModal.style.display = "none"
            loadingModal.hide()
            errModal.show()
        }
    } else if (document.getElementById('html') !== null) {
        var urlAddr = document.getElementById('html')
        if (document.getElementById('urlToPDF').value) {
            if (xhrBalance && xhrBalanceRemaining > 0) {
                procTitleMessageModal.innerText = "Processing URL..."
                errMessage.style.visibility = null
                errSubMessage.style.visibility = null
                errAltSubMessageModal.style.display = "none"
                errModal.hide()
                loadingModal.show()
                apiGateway("html","")
            } else {
                event.preventDefault()
                errMessage.innerText  = "PDF file can not be processed !"
                errSubMessage.innerText = ""
                errListTitleMessage.innerText = "Error message"
                resetErrListMessage()
                generateMesssage("Remaining monthly limit ("+xhrBalanceRemaining+" out of 250)")
                errAltSubMessageModal.style = null
                loadingModal.hide()
                errModal.show()
            }
        } else {
            event.preventDefault()
            errMessage.innerText  = "Please fill out these fields!"
            errSubMessage.innerText = ""
            errListTitleMessage.innerText = "Required fields:"
            resetErrListMessage()
            generateMesssage("URL Address")
            errAltSubMessageModal.style = null
            urlAddr.style.borderColor = '#A84E4E'
            loadingModal.hide()
            errModal.show()
        }
    }
}

function fileNameFormat(fileName) {
    let trimmedFileName = fileName.trim()
    let newFileName = trimmedFileName.replace(/\s+/g, '_')

    return newFileName;
}

function generateMesssage(subMessage) {
    var ul = document.getElementById("err-list")
    var li = document.createElement("li")
    li.appendChild(document.createTextNode(subMessage))
    ul.appendChild(li)
}

function getTotalPages(url) {
    return new Promise((resolve, reject) => {
      const loadingTask = pdfjsLib.getDocument(url)

      loadingTask.promise.then(
        (pdfDocument) => {
          const totalPages = pdfDocument.numPages
          resolve(totalPages)
        },
        (error) => {
          reject(error)
        }
      )
    })
}

function remainingBalance() {
    return new Promise(function (resolve, reject) {
        var xhr = new XMLHttpRequest()
        var formData = new FormData()
        xhr.open('GET', apiUrl+'/api/v1/logs/limit', true)
        xhr.setRequestHeader('Authorization', 'Bearer ' + bearerToken)
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')

        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    var xhrReturn = JSON.parse(xhr.responseText)
                    if (xhrReturn.status == 200) {
                        if (xhrReturn.remaining > 0) {
                            xhrBalance = true
                        } else {
                            xhrBalance = false
                        }
                        xhrBalanceRemaining = xhrReturn.remaining
                    } else {
                        xhrBalance = false
                        xhrBalanceRemaining = 0
                    }
                    resolve()
                } else {
                    xhrBalance = false
                    reject(new Error('Failed to fetch monthly limit'))
                }
            }
        }
        xhr.send(formData)
    })
}

function resetErrListMessage() {
    errListMessage.innerHTML = `
        <ul id="err-list"class="mt-1.5 list-disc list-inside font-bold"></ul>
    `
}
