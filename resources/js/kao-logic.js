import { Modal } from "flowbite";
import { Dropzone } from "dropzone";

const $errModal = document.getElementById("errModal");
const $loadingModal = document.getElementById("loadingModal");
const $previewModal = document.getElementById("previewModal");
const $scsModalNotify = document.getElementById("scsModalNotify");
const $previewDocumentModal = document.getElementById("previewDocumentModal");
const $previewImageModal = document.getElementById("previewImgModal");
const $versioningModal = document.getElementById("versioningModal");

const options = {
    placement: "bottom-right",
    backdrop: "dynamic",
    backdropClasses:
        "bg-gray-900 bg-opacity-50 backdrop-filter backdrop-blur-sm fixed inset-0 z-40",
    closable: true,
    onHide: () => {
        //console.log('modal is hidden')
    },
    onShow: () => {
        //console.log('modal is shown')
    },
    onToggle: () => {
        //console.log('modal has been toggled')
    },
};

const adobeClientID = "STATIC_CLIENT_ID";
const appMajorVer = 3;
const appMinorVer = 4;
const appPatchVer = 6;
const apiUrl = "STATIC_API";
const bearerToken = "STATIC_BEARER";
const errModal = new Modal($errModal, options);
const googleViewerUrl = "https://docs.google.com/viewerng/viewer?url=";
const loadingModal = new Modal($loadingModal, options);
const previewDocumentModal = new Modal($previewDocumentModal, options);
const previewImageModal = new Modal($previewImageModal, options);
const previewModal = new Modal($previewModal, options);
const procBtn = document.getElementById("submitBtn");
const scsModalNotify = new Modal($scsModalNotify, options);
const scsModalBtn = document.getElementById("scsModalBtn");
const uploadDropzone = document.getElementById("dropzoneArea");
const uploadDropzoneAlt = document.getElementById("dropzoneAreaCnv");
const uploadDropzoneSingle = document.getElementById("dropzoneAreaSingle");
const uploadPath = "/storage/upload/";
const versioningModal = new Modal($versioningModal, options);
const webContent = document.getElementById("footer");
const whatsNewBtn = document.getElementById("whatsNew");
let xhrBalance;
let xhrBalanceRemaining;
var altLoadingMessageModal = document.getElementById("altTitleMessageModal");
var errAltSubMessageModal = document.getElementById("altSubMessageModal");
var errMessage = document.getElementById("errMessageModal");
var errSubMessage = document.getElementById("errSubMessageModal");
var errListMessage = document.getElementById("err-list");
var errListTitleMessage = document.getElementById("err-list-title");
var scsMessage = document.getElementById("scsMessageModalNotify");
var procTitleMessageModal = document.getElementById("titleMessageModal");
var uploadedFile = [];
var uploadStats = false;
var xhrProcStats = true;
var xhrScsUploads = 0;
var xhrTotalUploads = 0;

if (procBtn) {
    procBtn.onclick = function (event) {
        if (procTitleMessageModal.innerText !== "Processing document") {
            procTitleMessageModal.innerText = "Preparing document";
        } else {
            if (
                document.getElementById("html") == null &&
                document.getElementById("cnvFrPDF") == null
            ) {
                procTitleMessageModal.innerText = "Processing document";
            } else {
                if (document.getElementById("html") !== null) {
                    procTitleMessageModal.innerText = "Processing URL";
                } else {
                    procTitleMessageModal.innerText = "Processing document";
                }
            }
        }
        errMessage.style.visibility = null;
        errSubMessage.style.visibility = null;
        errAltSubMessageModal.style.display = "none";
        errModal.hide();
        loadingModal.show();
        if (xhrProcStats) {
            validateVersion()
                .then(function () {
                    remainingBalance()
                        .then(function () {
                            loadingModal.hide();
                            if (document.getElementById("html") !== null) {
                                submit(event);
                            } else if (
                                xhrScsUploads > 0 &&
                                xhrTotalUploads > 0
                            ) {
                                if (xhrScsUploads == xhrTotalUploads) {
                                    submit(event);
                                } else {
                                    event.preventDefault();
                                    errMessage.innerText =
                                        "Sorry, we're still uploading your documents";
                                    errSubMessage.innerText = "";
                                    errListTitleMessage.innerText =
                                        "Error message";
                                    resetErrListMessage();
                                    generateMesssage(
                                        xhrScsUploads +
                                            " of " +
                                            xhrTotalUploads +
                                            " documents are still uploading"
                                    );
                                    errAltSubMessageModal.style = null;
                                    loadingModal.hide();
                                    errModal.show();
                                }
                            } else {
                                event.preventDefault();
                                errMessage.innerText =
                                    "Document can not be processed !";
                                errSubMessage.innerText = "";
                                errListTitleMessage.innerText = "Error message";
                                resetErrListMessage();
                                generateMesssage("No file has been chosen");
                                errAltSubMessageModal.style = null;
                                loadingModal.hide();
                                errModal.show();
                            }
                        })
                        .catch(function (error) {
                            errModal.hide();
                            errMessage.innerText =
                                "There was unexpected error !";
                            errSubMessage.innerText = "";
                            errListTitleMessage.innerText = "Error message";
                            resetErrListMessage();
                            generateMesssage(error.xhrBalanceResponse);
                            errAltSubMessageModal.style = null;
                            loadingModal.hide();
                            errModal.show();
                        });
                })
                .catch(function (error) {
                    errModal.hide();
                    errMessage.innerText = "There was unexpected error !";
                    errSubMessage.innerText = "";
                    errListTitleMessage.innerText = "Error message";
                    resetErrListMessage();
                    generateMesssage("Versioning validation failed");
                    generateMesssage(error.versioningMessage);
                    errAltSubMessageModal.style = null;
                    loadingModal.hide();
                    errModal.show();
                });
        } else {
            event.preventDefault();
            errMessage.innerText =
                "Sorry, we're still processing your documents";
            errSubMessage.style.visibility = null;
            errAltSubMessageModal.style.visibility = null;
            errAltSubMessageModal.style.display = "none";
            loadingModal.hide();
            errModal.show();
        }
    };
}

if (scsModalBtn) {
    scsModalBtn.onclick = function () {
        if (webContent) {
            webContent.scrollIntoView({ behavior: "smooth", block: "end" });
        }
    };
}

if (whatsNewBtn) {
    whatsNewBtn.onclick = function () {
        procTitleMessageModal.innerText = "Fetching latest update";
        loadingModal.show();
        fetchVersion()
            .then((data) => {
                const versionHistoryLayout = document.getElementById(
                    "versionHistoryLayout"
                );
                versionHistoryLayout.innerHTML = "";
                data.versionFetchResponse.forEach((versionInfo, index) => {
                    const changelogList = document.createElement("ul");
                    const versionDiv = document.createElement("div");
                    const versionTitle = document.createElement("h2");
                    const releaseDate = document.createElement("time");
                    changelogList.className =
                        "font-quicksand font-semibold text-sm text-dt1 list-disc list-inside overflow-y-auto mx-2";
                    versionDiv.className = "version-entry mb-4";
                    versionTitle.className =
                        "flex items-start mb-1 text-lg font-semibold font-quicksand text-pc";
                    releaseDate.className =
                        "block mb-3 text-sm font-normal leading-none font-quicksand text-dt3";
                    versionTitle.innerText = "Hana PDF v" + versionInfo.version;
                    if (index == 0) {
                        versionTitle.innerHTML +=
                            '<span class="bg-pc3 text-dt text-sm font-semibold font-quicksand mr-2 mt-0.5 px-2.5 py-0.5 rounded ms-3">Latest</span>';
                    }
                    releaseDate.innerText =
                        "Released on " + versionInfo.release_date;
                    versionInfo.changelog.forEach((change) => {
                        const listItem = document.createElement("li");
                        listItem.textContent = change;
                        changelogList.appendChild(listItem);
                    });
                    versionDiv.appendChild(versionTitle);
                    versionDiv.appendChild(releaseDate);
                    versionDiv.appendChild(changelogList);
                    versionHistoryLayout.appendChild(versionDiv);
                });

                procTitleMessageModal.innerText = "Preparing document";
                loadingModal.hide();
                versioningModal.show();
            })
            .catch(function (error) {
                procTitleMessageModal.innerText = "Preparing document";
                loadingModal.hide();
                errModal.hide();
                errMessage.innerText = "There was unexpected error !";
                errSubMessage.innerText = "";
                errListTitleMessage.innerText = "Error message";
                resetErrListMessage();
                generateMesssage(error.versionFetchMessage);
                errAltSubMessageModal.style = null;
                loadingModal.hide();
                errModal.show();
            });
    };
}

if (uploadDropzone) {
    let uploadDropzone = new Dropzone("#dropzoneArea", {
        url: `${apiUrl}/api/v1/file/upload`,
        paramName: "file",
        renameFile: function (file) {
            return fileNameFormat(fileNameFormat(file.name));
        },
        maxFilesize: 25,
        maxFiles: 5,
        acceptedFiles: "application/pdf",
        addRemoveLinks: true,
        dictDefaultMessage: "",
        dictRemoveFile: "Remove",
        timeout: 150000,
        previewTemplate:
            '<div class="dz-file-preview dz-preview dz-processing dz-success dz-complete z-0">' +
            '<div class="flex flex-col items-center justify-center">' +
            '<div class="mt-2 flex items-center justify-center lg:h-[200px] lg:w-[150px]">' +
            '<img id="imgThumbnail" class="dz-image-thumbnail h-48 w-32 object-scale-down" src="/assets/icons/placeholder_pdf.svg">' +
            "</div>" +
            '<div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>' +
            '<div class="dz-success-mark"><svg class="w-4 h-4 text-ac" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.5 11.5 11 14l4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg></div>' +
            '<div class="dz-error-mark"><svg class="w-4 h-4 text-rt1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"></path></svg></div>' +
            '<div class="dz-details -mt-8">' +
            '<div class="dz-filename font-sm font-magistral text-lt1"><span data-dz-name></span></div>' +
            "</div>" +
            '<div class="dz-error-message mt-2 ms-1 lg:ms-4"><span data-dz-errormessage></span></div>' +
            '<div class="flex flex-row mx-auto">' +
            '<button type="button" id="prvBtn" class="prvBtn mt-2 mx-4 p-2 bg-pc2 text-lt rounded-lg cursor-pointer w-8 h-8 text-center flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-white"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg></button>' +
            '<button type="button" id="rmvBtn" class="rmvBtn mt-2 mx-4 p-2 bg-rt1 text-lt rounded-lg cursor-pointer w-8 h-8 text-center flex items-center justify-center" data-dz-remove=""><svg class="w-6 h-6 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z"></path></svg></button>' +
            "</div>" +
            "</div>" +
            "</div>",
        clickable: true,
        headers: {
            Authorization: "Bearer " + bearerToken,
            "X-Requested-With": "XMLHttpRequest",
            Accept: "application/json",
        },
        init: function () {
            document
                .getElementById("dropzoneUploadInit")
                .addEventListener("click", function () {
                    uploadDropzone.hiddenFileInput.click();
                });

            document
                .getElementById("dropzoneUploadExt")
                .addEventListener("click", function () {
                    if (uploadDropzone.files.length >= 4) {
                        document
                            .getElementById("dropzoneUiExt")
                            .classList.add("hidden");
                    } else {
                        uploadDropzone.hiddenFileInput.click();
                        document
                            .getElementById("dropzoneUiExt")
                            .classList.remove("hidden");
                    }
                });

            this.on("addedfile", function (file) {
                xhrTotalUploads = uploadDropzone.files.length;
                var rmvLink = document.getElementsByClassName("dz-remove");
                var dzFileLayout =
                    document.querySelectorAll('[data-dz-name=""]');

                document.querySelector(".dz-default.dz-message").style.display =
                    "none";
                document.getElementById("dropzoneUiInit").style.display =
                    "none";

                for (var i = 0; i < rmvLink.length; i++) {
                    rmvLink[i].style.display = "none";
                }

                dzFileLayout.forEach(function (element) {
                    Object.assign(element.style, {
                        backgroundColor: "transparent",
                        borderColor: "transparent",
                    });
                });

                if (file.type === "application/pdf") {
                    generatePdfThumbnail(file);

                    if (uploadDropzone.files.length >= 4) {
                        document
                            .getElementById("dropzoneUiExt")
                            .classList.add("hidden");
                        uploadDropzone.hiddenFileInput.setAttribute(
                            "disabled",
                            "disabled"
                        );
                    } else {
                        document
                            .getElementById("dropzoneUiExt")
                            .classList.remove("hidden");
                        uploadDropzone.hiddenFileInput.removeAttribute(
                            "disabled",
                            "disabled"
                        );
                    }

                    Object.assign(procBtn.style, {
                        backgroundColor: "#4DAAAA",
                        borderColor: "transparent",
                    });

                    var prvBtn = document.querySelectorAll(".prvBtn");
                    var rmvBtn = document.querySelectorAll(".rmvBtn");
                    prvBtn.forEach(function (button) {
                        button.addEventListener("click", function (event) {
                            var parentContainer =
                                event.target.closest(".dz-file-preview");
                            var filenameElement =
                                parentContainer.querySelector(
                                    ".dz-filename span"
                                );
                            var uploadedFile1 = fileNameFormat(
                                filenameElement.innerText
                            );
                            getTemporaryURL(uploadedFile1)
                                .then(function (temporaryURL) {
                                    var newUrl = temporaryURL.temporaryURL;
                                    var adobeDCView = new AdobeDC.View({
                                        clientId: adobeClientID,
                                        divId: "adobe-dc-view",
                                    });
                                    adobeDCView.previewFile(
                                        {
                                            content: {
                                                location: {
                                                    url: newUrl,
                                                },
                                            },
                                            metaData: {
                                                fileName: uploadedFile1,
                                            },
                                        },
                                        {
                                            embedMode: "SIZED_CONTAINER",
                                            focusOnRendering: true,
                                            showDownloadPDF: false,
                                        }
                                    );
                                    previewModal.show();
                                })
                                .catch(function (temporaryURL) {
                                    errMessage.innerText = `File ${uploadedFile1} not found !`;
                                    errSubMessage.innerText = "";
                                    errListTitleMessage.innerText =
                                        "Error message";
                                    resetErrListMessage();
                                    generateMesssage(
                                        temporaryURL.temporaryError
                                    );
                                    errAltSubMessageModal.style = null;
                                    previewDocumentModal.hide();
                                    errModal.show();
                                });
                        });
                    });
                    rmvBtn.forEach(function (button) {
                        button.addEventListener("click", function () {
                            var adobeScript =
                                document.getElementById("adobe-dc-view");
                            if (adobeScript) {
                                adobeScript.innerHTML =
                                    '<script src="https://acrobatservices.adobe.com/view-sdk/viewer.js"></script>';
                            }

                            if (uploadDropzone.files.length > 3) {
                                document
                                    .getElementById("dropzoneUiExt")
                                    .classList.add("hidden");
                            } else if (
                                uploadDropzone.files.length > 0 &&
                                uploadDropzone.files.length < 5
                            ) {
                                uploadDropzone.hiddenFileInput.removeAttribute(
                                    "disabled",
                                    "disabled"
                                );
                                document
                                    .getElementById("dropzoneUiExt")
                                    .classList.remove("hidden");
                            }
                        });
                    });
                }
            });

            this.on("removedfile", function (file) {
                if (uploadDropzone.files.length === 0) {
                    document.getElementById("dropzoneUiInit").style.display =
                        null;
                    document
                        .getElementById("dropzoneUiExt")
                        .classList.add("hidden");
                    Object.assign(procBtn.style, {
                        backgroundColor: null,
                        borderColor: "#E0E4E5",
                        color: null,
                    });
                }

                if (file) {
                    const filePath = uploadPath + fileNameFormat(file.name);

                    if (xhrScsUploads > 0) {
                        let dzErrorMessage =
                            file.previewElement.querySelector(
                                ".dz-error-message"
                            );
                        if (dzErrorMessage.textContent == "") {
                            xhrScsUploads = xhrScsUploads - 1;
                        }
                        uploadedFile = uploadedFile.filter(
                            (item) => !fileNameFormat(file.name).includes(item)
                        );
                    }

                    if (xhrTotalUploads > 0) {
                        xhrTotalUploads = xhrTotalUploads - 1;
                    }

                    fetch(`${apiUrl}/api/v1/file/remove`, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            Authorization: `Bearer ${bearerToken}`,
                            "X-Requested-With": "XMLHttpRequest",
                            Accept: "application/json",
                            file: filePath,
                        },
                        body: JSON.stringify({
                            file: filePath,
                        }),
                    })
                        .then((response) => response.json())
                        .catch((error) => {
                            console.error(
                                "Error: Failed to remove file: ",
                                error
                            );
                        });
                } else {
                    console.error("Error: File object is null or undefined.");
                }
            });

            this.on("success", function (response) {
                var uploadedFileName = fileNameFormat(response.name);
                uploadedFile.push(uploadedFileName);

                xhrScsUploads = xhrScsUploads + 1;

                if (xhrScsUploads == xhrTotalUploads) {
                    uploadStats = true;
                } else {
                    uploadStats = false;
                }
            });

            this.on("error", function (file, dropzoneErrMessage, xhr) {
                const dzErrorMessage =
                    file.previewElement.querySelector(".dz-error-message");
                let newErrMessage = "Internal server error";

                if (xhr.readyState === XMLHttpRequest.DONE) {
                    try {
                        const xhrResponse = JSON.parse(xhr.responseText);
                        newErrMessage =
                            xhrResponse.errors ||
                            "There was an unexpected error";
                    } catch (error) {
                        newErrMessage = "Failed to parse server response";
                    }
                } else {
                    newErrMessage =
                        dropzoneErrMessage || "Server was not ready";
                }

                dzErrorMessage.textContent = newErrMessage;

                errMessage.innerText = `Failed to upload ${file.name}`;
                errSubMessage.innerText = "";
                errListTitleMessage.innerText = "Error message";
                resetErrListMessage();
                generateMesssage(dzErrorMessage?.textContent || newErrMessage);
                errAltSubMessageModal.style = null;
                loadingModal.hide();
                errModal.show();
            });

            this.on("timeout", function (file) {
                uploadDropzone.removeFile(file);
                uploadedFile = uploadedFile.filter(
                    (item) => !fileNameFormat(file.name).includes(item)
                );
                errMessage.innerText = "Connection timeout !";
                errSubMessage.innerText = "Please try again later";
                errListTitleMessage.innerText = "Failed to upload:";
                resetErrListMessage();
                generateMesssage(file.name);
                errAltSubMessageModal.style = null;
                errModal.show();
            });
        },
    });

    if (!uploadDropzone) {
        if (procBtn) {
            if (
                document.getElementById("compress") !== null ||
                document.getElementById("cnvFrPDF") !== null ||
                document.getElementById("merge") !== null
            ) {
                errMessage.innerText = "There was unexpected error !";
                errSubMessage.innerText = "";
                errListTitleMessage.innerText = "Error message";
                resetErrListMessage();
                generateMesssage("Cannot establish connection with the server");
                errAltSubMessageModal.style = null;
                loadingModal.hide();
                errModal.show();
            }
        }
    }
}

if (uploadDropzoneAlt) {
    let uploadDropzoneAlt = new Dropzone("#dropzoneAreaCnv", {
        url: `${apiUrl}/api/v1/file/upload`,
        paramName: "file",
        renameFile: function (file) {
            return fileNameFormat(file.name);
        },
        maxFilesize: 25,
        maxFiles: 5,
        acceptedFiles: ".xlsx, .xls, .ppt, .pptx, .docx, .doc, image/*",
        addRemoveLinks: true,
        dictDefaultMessage: "",
        dictRemoveFile: "Remove",
        timeout: 15000,
        previewTemplate:
            '<div class="dz-file-preview dz-preview dz-processing dz-success dz-complete z-0">' +
            '<div class="flex flex-col items-center justify-center">' +
            '<div class="mt-2 flex items-center justify-center lg:h-[200px] lg:w-[150px]">' +
            '<div id="loadingThumbnail" class="animate-spin-counter-clockwise inline-block p-4 mx-4 w-24 h-24 object-scale-down" role="status" aria-label="loading">' +
            `<img src="/assets/icons/process.svg" alt="loading">` +
            "</div>" +
            '<img id="imgThumbnail" class="dz-image-thumbnail h-48 w-32 object-scale-down hidden" src="/assets/icons/placeholder_pdf.svg">' +
            "</div>" +
            '<div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>' +
            '<div class="dz-success-mark"><svg class="w-4 h-4 text-ac" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.5 11.5 11 14l4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg></div>' +
            '<div class="dz-error-mark"><svg class="w-4 h-4 text-rt1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"></path></svg></div>' +
            '<div class="dz-details -mt-8">' +
            '<div class="dz-filename font-sm font-magistral text-lt1"><span data-dz-name></span></div>' +
            "</div>" +
            '<div class="dz-error-message mt-2 ms-1 lg:ms-4"><span data-dz-errormessage></span></div>' +
            '<div class="flex flex-row mx-auto">' +
            '<button type="button" id="prvBtn" class="prvBtn mt-2 mx-4 p-2 bg-pc2 text-lt rounded-lg cursor-pointer w-8 h-8 text-center flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-white"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg></button>' +
            '<button type="button" id="rmvBtn" class="rmvBtn mt-2 mx-4 p-2 bg-rt1 text-lt rounded-lg cursor-pointer w-8 h-8 text-center flex items-center justify-center" data-dz-remove=""><svg class="w-6 h-6 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z"></path></svg></button>' +
            "</div>" +
            "</div>" +
            "</div>",
        clickable: true,
        headers: {
            Authorization: "Bearer " + bearerToken,
            "X-Requested-With": "XMLHttpRequest",
            Accept: "application/json",
        },
        init: function () {
            document
                .getElementById("dropzoneUploadInit")
                .addEventListener("click", function () {
                    uploadDropzoneAlt.hiddenFileInput.click();
                });

            document
                .getElementById("dropzoneUploadExt")
                .addEventListener("click", function () {
                    if (uploadDropzoneAlt.files.length >= 4) {
                        document
                            .getElementById("dropzoneUiExt")
                            .classList.add("hidden");
                    } else {
                        uploadDropzoneAlt.hiddenFileInput.click();
                        document
                            .getElementById("dropzoneUiExt")
                            .classList.remove("hidden");
                    }
                });

            this.on("addedfile", function (file) {
                xhrTotalUploads = uploadDropzoneAlt.files.length;

                var rmvLink = document.getElementsByClassName("dz-remove");
                var dzFileLayout =
                    document.querySelectorAll('[data-dz-name=""]');

                document.querySelector(".dz-default.dz-message").style.display =
                    "none";
                document.getElementById("dropzoneUiInit").style.display =
                    "none";

                for (var i = 0; i < rmvLink.length; i++) {
                    rmvLink[i].style.display = "none";
                }

                dzFileLayout.forEach(function (element) {
                    Object.assign(element.style, {
                        backgroundColor: "#transparent",
                        borderColor: "transparent",
                    });
                });

                if (uploadDropzoneAlt.files.length >= 4) {
                    document
                        .getElementById("dropzoneUiExt")
                        .classList.add("hidden");
                    uploadDropzoneAlt.hiddenFileInput.setAttribute(
                        "disabled",
                        "disabled"
                    );
                } else {
                    Object.assign(procBtn.style, {
                        backgroundColor: "#4DAAAA",
                        borderColor: "transparent",
                    });
                    document
                        .getElementById("dropzoneUiExt")
                        .classList.remove("hidden");
                    uploadDropzoneAlt.hiddenFileInput.removeAttribute(
                        "disabled",
                        "disabled"
                    );
                }

                var prvBtn = document.querySelectorAll(".prvBtn");
                var rmvBtn = document.querySelectorAll(".rmvBtn");
                prvBtn.forEach(function (button) {
                    button.addEventListener("click", function (event) {
                        var parentContainer =
                            event.target.closest(".dz-preview");
                        var filenameElement =
                            parentContainer.querySelector(".dz-filename span");
                        var uploadedFile1 = fileNameFormat(
                            filenameElement.innerText
                        );
                        getTemporaryURL(uploadedFile1)
                            .then(function (temporaryURL) {
                                if (checkImageExtensions(uploadedFile1)) {
                                    var imageUrl = temporaryURL.temporaryURL;
                                    document.getElementById("imgPrv").src =
                                        imageUrl;
                                    previewImageModal.show();
                                } else {
                                    var documentUrl =
                                        googleViewerUrl +
                                        temporaryURL.temporaryURL +
                                        "&embedded=true";
                                    document.getElementById("iFrame").src =
                                        documentUrl;
                                    previewDocumentModal.show();
                                }
                            })
                            .catch(function (temporaryURL) {
                                errMessage.innerText = `File ${uploadedFile1} not found !`;
                                errSubMessage.innerText = "";
                                errListTitleMessage.innerText = "Error message";
                                resetErrListMessage();
                                generateMesssage(temporaryURL.temporaryError);
                                errAltSubMessageModal.style = null;
                                previewDocumentModal.hide();
                                errModal.show();
                            });
                    });
                });
                rmvBtn.forEach(function (button) {
                    button.addEventListener("click", function () {
                        if (uploadDropzoneAlt.files.length > 3) {
                            document
                                .getElementById("dropzoneUiExt")
                                .classList.add("hidden");
                        } else if (
                            uploadDropzoneAlt.files.length > 0 &&
                            uploadDropzoneAlt.files.length < 5
                        ) {
                            uploadDropzoneAlt.hiddenFileInput.removeAttribute(
                                "disabled",
                                "disabled"
                            );
                            document
                                .getElementById("dropzoneUiExt")
                                .classList.remove("hidden");
                        }
                    });
                });
            });

            this.on("removedfile", function (file) {
                if (uploadDropzoneAlt.files.length === 0) {
                    document.getElementById("dropzoneUiInit").style.display =
                        null;
                    document
                        .getElementById("dropzoneUiExt")
                        .classList.add("hidden");
                    Object.assign(procBtn.style, {
                        backgroundColor: null,
                        borderColor: "#E0E4E5",
                        color: null,
                    });
                }

                if (file) {
                    const filePath = uploadPath + fileNameFormat(file.name);

                    if (xhrScsUploads > 0) {
                        let dzErrorMessage =
                            file.previewElement.querySelector(
                                ".dz-error-message"
                            );
                        if (dzErrorMessage.textContent == "") {
                            xhrScsUploads = xhrScsUploads - 1;
                        }
                        uploadedFile = uploadedFile.filter(
                            (item) => !fileNameFormat(file.name).includes(item)
                        );
                    }

                    if (xhrTotalUploads > 0) {
                        xhrTotalUploads = xhrTotalUploads - 1;
                    }

                    fetch(`${apiUrl}/api/v1/file/remove`, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            Authorization: `Bearer ${bearerToken}`,
                            "X-Requested-With": "XMLHttpRequest",
                            Accept: "application/json",
                            file: filePath,
                        },
                        body: JSON.stringify({
                            file: filePath,
                        }),
                    })
                        .then((response) => response.json())
                        .catch((error) => {
                            console.error(
                                "Error: Failed to remove file: ",
                                error
                            );
                        });
                } else {
                    console.error("Error: File object is null or undefined.");
                }
            });

            this.on("success", function (file) {
                var uploadedFileName = fileNameFormat(file.name);
                uploadedFile.push(uploadedFileName);

                xhrScsUploads = xhrScsUploads + 1;

                if (xhrScsUploads == xhrTotalUploads) {
                    uploadStats = true;
                } else {
                    uploadStats = false;
                }

                if (!file.type.startsWith("image/")) {
                    generateThumbnail(fileNameFormat(file.name))
                        .then(function (thumbnailURL) {
                            file.previewElement
                                .querySelector("#loadingThumbnail")
                                .classList.add("hidden");
                            file.previewElement
                                .querySelector("#imgThumbnail")
                                .classList.remove("hidden");
                            if (
                                file.type ==
                                "application/vnd.openxmlformats-officedocument.presentationml.presentation"
                            )
                                file.previewElement.querySelector(
                                    ".dz-image-thumbnail"
                                ).src = "/assets/icons/placeholder_pptx.svg";
                            else
                                file.previewElement.querySelector(
                                    ".dz-image-thumbnail"
                                ).src = thumbnailURL;
                        })
                        .catch(function () {
                            file.previewElement
                                .querySelector("#loadingThumbnail")
                                .classList.add("hidden");
                            file.previewElement
                                .querySelector("#imgThumbnail")
                                .classList.remove("hidden");
                            file.previewElement.querySelector(
                                ".dz-image-thumbnail"
                            ).src = "/assets/icons/placeholder_pptx.svg";
                        });
                } else if (file.type.startsWith("image/")) {
                    getTemporaryURL(fileNameFormat(file.name))
                        .then(function (temporaryURL) {
                            file.previewElement
                                .querySelector("#loadingThumbnail")
                                .classList.add("hidden");
                            file.previewElement
                                .querySelector("#imgThumbnail")
                                .classList.remove("hidden");
                            var newUrl = temporaryURL.temporaryURL;
                            file.previewElement.querySelector(
                                ".dz-image-thumbnail"
                            ).src = newUrl;
                        })
                        .catch(function (temporaryURL) {
                            file.previewElement
                                .querySelector("#loadingThumbnail")
                                .classList.add("hidden");
                            file.previewElement
                                .querySelector("#imgThumbnail")
                                .classList.remove("hidden");
                            file.previewElement.querySelector(
                                ".dz-image-thumbnail"
                            ).src = "/assets/icons/placeholder_pptx.svg";
                            errMessage.innerText = `Failed to generated thumbnail for ${file.name}`;
                            errSubMessage.innerText = "";
                            errListTitleMessage.innerText = "Error message";
                            resetErrListMessage();
                            generateMesssage(temporaryURL.temporaryError);
                            errAltSubMessageModal.style = null;
                            previewDocumentModal.hide();
                            errModal.show();
                        });
                }
            });

            this.on("error", function (file, dropzoneErrMessage, xhr) {
                const dzErrorMessage =
                    file.previewElement.querySelector(".dz-error-message");
                let newErrMessage = "Internal server error";

                if (xhr.readyState === XMLHttpRequest.DONE) {
                    try {
                        const xhrResponse = JSON.parse(xhr.responseText);
                        newErrMessage =
                            xhrResponse.errors ||
                            "There was an unexpected error";
                    } catch (error) {
                        newErrMessage = "Failed to parse server response";
                    }
                } else {
                    newErrMessage =
                        dropzoneErrMessage || "Server was not ready";
                }

                dzErrorMessage.textContent = newErrMessage;

                file.previewElement
                    .querySelector("#loadingThumbnail")
                    .classList.add("hidden");
                file.previewElement
                    .querySelector("#imgThumbnail")
                    .classList.remove("hidden");

                errMessage.innerText = `Failed to upload ${file.name}`;
                errSubMessage.innerText = "";
                errListTitleMessage.innerText = "Error message";
                resetErrListMessage();
                generateMesssage(dzErrorMessage?.textContent || newErrMessage);
                errAltSubMessageModal.style = null;
                loadingModal.hide();
                errModal.show();
            });

            this.on("timeout", function (file) {
                uploadDropzoneAlt.removeFile(file);
                uploadedFile = uploadedFile.filter(
                    (item) => !fileNameFormat(file.name).includes(item)
                );
                file.previewElement
                    .querySelector("#loadingThumbnail")
                    .classList.add("hidden");
                file.previewElement
                    .querySelector("#imgThumbnail")
                    .classList.remove("hidden");
                errMessage.innerText = "Connection timeout !";
                errSubMessage.innerText = "Please try again later";
                errListTitleMessage.innerText = "Failed to upload:";
                resetErrListMessage();
                generateMesssage(file.name);
                errAltSubMessageModal.style = null;
                errModal.show();
            });
        },
    });

    if (!uploadDropzoneAlt) {
        if (procBtn && document.getElementById("cnvToPDF") !== null) {
            errMessage.innerText = "There was unexpected error !";
            errSubMessage.innerText = "";
            errListTitleMessage.innerText = "Error message";
            resetErrListMessage();
            generateMesssage("Cannot establish connection with the server");
            errAltSubMessageModal.style = null;
            loadingModal.hide();
            errModal.show();
        }
    }
}

if (uploadDropzoneSingle) {
    let uploadDropzoneSingle = new Dropzone("#dropzoneAreaSingle", {
        url: `${apiUrl}/api/v1/file/upload`,
        paramName: "file",
        renameFile: function (file) {
            return fileNameFormat(file.name);
        },
        maxFilesize: 25,
        maxFiles: 1,
        acceptedFiles: "application/pdf",
        addRemoveLinks: true,
        dictDefaultMessage: "",
        dictRemoveFile: "Remove",
        timeout: 15000,
        previewTemplate:
            '<div class="dz-file-preview dz-preview dz-processing dz-success dz-complete z-0">' +
            '<div class="flex flex-col items-center justify-center">' +
            '<div class="mt-2 flex items-center justify-center lg:h-[200px] lg:w-[150px]">' +
            '<img id="imgThumbnail" class="dz-image-thumbnail h-48 w-32 object-scale-down" src="/assets/icons/placeholder_pdf.svg">' +
            "</div>" +
            '<div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>' +
            '<div class="dz-success-mark"><svg class="w-4 h-4 text-ac" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.5 11.5 11 14l4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg></div>' +
            '<div class="dz-error-mark"><svg class="w-4 h-4 text-rt1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"></path></svg></div>' +
            '<div class="dz-details -mt-8">' +
            '<div class="dz-filename font-sm font-magistral text-lt1"><span data-dz-name></span></div>' +
            "</div>" +
            '<div class="dz-error-message mt-2 ms-1 lg:ms-4"><span data-dz-errormessage></span></div>' +
            '<div class="flex flex-row mx-auto">' +
            '<button type="button" id="prvBtn" class="prvBtn mt-2 mx-4 p-2 bg-pc2 text-lt rounded-lg cursor-pointer w-8 h-8 text-center flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-white"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg></button>' +
            '<button type="button" id="rmvBtn" class="rmvBtn mt-2 mx-4 p-2 bg-rt1 text-lt rounded-lg cursor-pointer w-8 h-8 text-center flex items-center justify-center" data-dz-remove=""><svg class="w-6 h-6 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z"></path></svg></button>' +
            "</div>" +
            "</div>" +
            "</div>",
        clickable: true,
        headers: {
            Authorization: "Bearer " + bearerToken,
            "X-Requested-With": "XMLHttpRequest",
            Accept: "application/json",
        },
        init: function () {
            document
                .getElementById("dropzoneUploadInit")
                .addEventListener("click", function () {
                    uploadDropzoneSingle.hiddenFileInput.click();
                });

            document
                .getElementById("dropzoneUploadExt")
                .addEventListener("click", function () {
                    if (uploadDropzoneSingle.files.length >= 1) {
                        document
                            .getElementById("dropzoneUiExt")
                            .classList.add("hidden");
                    } else {
                        uploadDropzoneSingle.hiddenFileInput.click();
                        document
                            .getElementById("dropzoneUiExt")
                            .classList.remove("hidden");
                    }
                });

            this.on("addedfile", function (file) {
                xhrTotalUploads = uploadDropzoneSingle.files.length;

                var rmvLink = document.getElementsByClassName("dz-remove");
                var dzFileLayout =
                    document.querySelectorAll('[data-dz-name=""]');

                document.querySelector(".dz-default.dz-message").style.display =
                    "none";
                document.getElementById("dropzoneUiInit").style.display =
                    "none";

                for (var i = 0; i < rmvLink.length; i++) {
                    rmvLink[i].style.display = "none";
                }

                dzFileLayout.forEach(function (element) {
                    Object.assign(element.style, {
                        backgroundColor: "#4DAAAA",
                        borderColor: "transparent",
                    });
                });

                if (file.type === "application/pdf") {
                    generatePdfThumbnail(file);

                    if (uploadDropzoneSingle.files.length >= 1) {
                        document
                            .getElementById("dropzoneUiExt")
                            .classList.add("hidden");
                        uploadDropzoneSingle.hiddenFileInput.setAttribute(
                            "disabled",
                            "disabled"
                        );
                    } else {
                        document
                            .getElementById("dropzoneUiExt")
                            .classList.remove("hidden");
                        uploadDropzoneSingle.hiddenFileInput.removeAttribute(
                            "disabled",
                            "disabled"
                        );
                    }

                    Object.assign(procBtn.style, {
                        backgroundColor: "#4DAAAA",
                        color: "#E0E4E5",
                    });

                    var prvBtn = document.querySelectorAll(".prvBtn");
                    var rmvBtn = document.querySelectorAll(".rmvBtn");
                    prvBtn.forEach(function (button) {
                        button.addEventListener("click", function (event) {
                            var parentContainer =
                                event.target.closest(".dz-file-preview");
                            var filenameElement =
                                parentContainer.querySelector(
                                    ".dz-filename span"
                                );
                            var uploadedFile1 = fileNameFormat(
                                filenameElement.innerText
                            );
                            getTemporaryURL(uploadedFile1)
                                .then(function (temporaryURL) {
                                    var newUrl = temporaryURL.temporaryURL;
                                    var adobeDCView = new AdobeDC.View({
                                        clientId: adobeClientID,
                                        divId: "adobe-dc-view",
                                    });
                                    adobeDCView.previewFile(
                                        {
                                            content: {
                                                location: {
                                                    url: newUrl,
                                                },
                                            },
                                            metaData: {
                                                fileName: uploadedFile1,
                                            },
                                        },
                                        {
                                            embedMode: "SIZED_CONTAINER",
                                            focusOnRendering: true,
                                            showDownloadPDF: false,
                                        }
                                    );
                                    previewModal.show();
                                })
                                .catch(function (temporaryURL) {
                                    errMessage.innerText = `File ${uploadedFile1} not found !`;
                                    errSubMessage.innerText = "";
                                    errListTitleMessage.innerText =
                                        "Error message";
                                    resetErrListMessage();
                                    generateMesssage(
                                        temporaryURL.temporaryError
                                    );
                                    errAltSubMessageModal.style = null;
                                    previewDocumentModal.hide();
                                    errModal.show();
                                });
                        });
                    });
                    rmvBtn.forEach(function (button) {
                        button.addEventListener("click", function () {
                            var adobeScript =
                                document.getElementById("adobe-dc-view");
                            if (adobeScript) {
                                adobeScript.innerHTML =
                                    '<script src="https://acrobatservices.adobe.com/view-sdk/viewer.js"></script>';
                            }

                            if (uploadDropzoneSingle.files.length >= 1) {
                                uploadDropzoneSingle.hiddenFileInput.removeAttribute(
                                    "disabled",
                                    "disabled"
                                );
                                document
                                    .getElementById("dropzoneUiExt")
                                    .classList.remove("hidden");
                            } else {
                                document
                                    .getElementById("dropzoneUiExt")
                                    .classList.add("hidden");
                            }
                        });
                    });
                }
            });

            this.on("removedfile", function (file) {
                if (uploadDropzoneSingle.files.length === 0) {
                    document.getElementById("dropzoneUiInit").style.display =
                        null;
                    document
                        .getElementById("dropzoneUiExt")
                        .classList.add("hidden");
                    Object.assign(procBtn.style, {
                        backgroundColor: null,
                        color: null,
                    });
                }

                if (file) {
                    const filePath = uploadPath + fileNameFormat(file.name);

                    if (xhrScsUploads > 0) {
                        let dzErrorMessage =
                            file.previewElement.querySelector(
                                ".dz-error-message"
                            );
                        if (dzErrorMessage.textContent == "") {
                            xhrScsUploads = xhrScsUploads - 1;
                        }
                        uploadedFile = uploadedFile.filter(
                            (item) => !fileNameFormat(file.name).includes(item)
                        );
                    }

                    if (xhrTotalUploads > 0) {
                        xhrTotalUploads = xhrTotalUploads - 1;
                    }

                    fetch(`${apiUrl}/api/v1/file/remove`, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            Authorization: `Bearer ${bearerToken}`,
                            "X-Requested-With": "XMLHttpRequest",
                            Accept: "application/json",
                            file: filePath,
                        },
                        body: JSON.stringify({
                            file: filePath,
                        }),
                    })
                        .then((response) => response.json())
                        .catch((error) => {
                            console.error(
                                "Error: Failed to remove file: ",
                                error
                            );
                        });
                } else {
                    console.error("Error: File object is null or undefined.");
                }
            });

            this.on("success", function (response) {
                var uploadedFileName = fileNameFormat(response.name);
                uploadedFile.push(uploadedFileName);

                xhrScsUploads = xhrScsUploads + 1;

                if (xhrScsUploads == xhrTotalUploads) {
                    uploadStats = true;
                } else {
                    uploadStats = false;
                }
            });

            this.on("error", function (file, dropzoneErrMessage, xhr) {
                const dzErrorMessage =
                    file.previewElement.querySelector(".dz-error-message");
                let newErrMessage = "Internal server error";
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    try {
                        const xhrResponse = JSON.parse(xhr.responseText);
                        newErrMessage =
                            xhrResponse.errors ||
                            "There was an unexpected error";
                    } catch (error) {
                        newErrMessage = "Failed to parse server response";
                    }
                } else {
                    newErrMessage =
                        dropzoneErrMessage || "Server was not ready";
                }

                dzErrorMessage.textContent = newErrMessage;

                errMessage.innerText = `Failed to upload ${file.name}`;
                errSubMessage.innerText = "";
                errListTitleMessage.innerText = "Error message";
                resetErrListMessage();
                generateMesssage(dzErrorMessage?.textContent || newErrMessage);
                errAltSubMessageModal.style = null;
                loadingModal.hide();
                errModal.show();
            });

            this.on("timeout", function (file) {
                uploadDropzoneSingle.removeFile(file);
                uploadedFile = uploadedFile.filter(
                    (item) => !fileNameFormat(file.name).includes(item)
                );
                errMessage.innerText = "Connection timeout !";
                errSubMessage.innerText = "Please try again later";
                errListTitleMessage.innerText = "Failed to upload:";
                resetErrListMessage();
                generateMesssage(file.name);
                errAltSubMessageModal.style = null;
                errModal.show();
            });
        },
    });

    if (!uploadDropzoneSingle) {
        if (procBtn) {
            if (
                document.getElementById("split") !== null ||
                document.getElementById("watermark") !== null
            ) {
                errMessage.innerText = "There was unexpected error !";
                errSubMessage.innerText = "";
                errListTitleMessage.innerText = "Error message";
                resetErrListMessage();
                generateMesssage("Cannot establish connection with the server");
                errAltSubMessageModal.style = null;
                loadingModal.hide();
                errModal.show();
            }
        }
    }
}

function apiGateway(proc, action) {
    var files = getUploadedFileName();
    sendToAPI(files, proc, action)
        .then(function () {
            xhrProcStats = true;
            closeAltModal(proc);
            loadingModal.hide();
            errModal.hide();
            if (document.getElementById("html") == null) {
                scsMessage.innerText = "Document " + proc + " success !";
            } else {
                if (document.getElementById("html") !== null) {
                    scsMessage.innerText = "URL convert success !";
                }
            }
            scsModalNotify.show();
        })
        .catch(function (error) {
            xhrProcStats = true;
            closeAltModal(proc);
            errModal.hide();
            errMessage.innerText = "There was unexpected error !";
            errSubMessage.innerText = "";
            errListTitleMessage.innerText = "Error message";
            resetErrListMessage();
            generateMesssage(error.xhrRequestServerMessage);
            errAltSubMessageModal.style = null;
            loadingModal.hide();
            errModal.show();
        });
}

function closeAltModal(proc) {
    document.getElementById("altLoadingModal").classList.add("hidden");
    if (proc == "html") {
        document.getElementById("formHTML").classList.remove("animate-pulse");
    } else {
        if (proc == "compress") {
            document
                .getElementById("dropzoneCmp")
                .classList.remove("animate-pulse");
        } else if (proc == "convert") {
            if (document.getElementById("cnvFrPDF") !== null) {
                document
                    .getElementById("dropzoneCnvFrPDF")
                    .classList.remove("animate-pulse");
            } else {
                document
                    .getElementById("dropzoneCnvToPDF")
                    .classList.remove("animate-pulse");
            }
        } else if (proc == "merge") {
            document
                .getElementById("dropzoneMerge")
                .classList.remove("animate-pulse");
        } else if (proc == "split") {
            document
                .getElementById("dropzoneSplit")
                .classList.remove("animate-pulse");
        } else {
            document
                .getElementById("dropzoneWatermark")
                .classList.remove("animate-pulse");
        }
    }
}

function checkImageExtensions(url) {
    const extension = /\.(jpg|jpeg|png|gif|bmp)$/i;
    return extension.test(url);
}

function generatePdfThumbnail(file) {
    return new Promise((resolve, reject) => {
        const fileReader = new FileReader();

        fileReader.onload = function () {
            try {
                const typedArray = new Uint8Array(this.result);

                pdfjsLib
                    .getDocument(typedArray)
                    .promise.then(function (pdf) {
                        return pdf.getPage(1);
                    })
                    .then(function (page) {
                        const canvas = document.createElement("canvas");
                        const ctx = canvas.getContext("2d");
                        const viewport = page.getViewport({ scale: 0.5 });

                        canvas.width = viewport.width;
                        canvas.height = viewport.height;

                        const renderContext = {
                            canvasContext: ctx,
                            viewport: viewport,
                        };

                        page.render(renderContext)
                            .promise.then(function () {
                                const thumbnail =
                                    canvas.toDataURL("image/jpeg");
                                const previewElement = file.previewElement;
                                const dzImage = previewElement.querySelector(
                                    ".dz-image-thumbnail"
                                );
                                dzImage.src = thumbnail;
                            })
                            .catch((error) => {
                                reject(error);
                            });
                    })
                    .catch((error) => {
                        reject(error);
                    });
            } catch (error) {
                reject(error);
            }
        };

        fileReader.onerror = function (error) {
            reject(error);
        };

        fileReader.readAsArrayBuffer(file);
    });
}

function getUploadedFileName() {
    return uploadedFile;
}

function fetchVersion() {
    return new Promise(function (resolve, reject) {
        var xhr = new XMLHttpRequest();

        xhr.open("GET", `${apiUrl}/api/v1/version/fetch`, true);
        xhr.setRequestHeader("Authorization", "Bearer " + bearerToken);
        xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        xhr.setRequestHeader("Accept", "application/json; charset=utf-8");

        xhr.onload = () => {
            try {
                const xhrReturn = JSON.parse(xhr.responseText);
                if (xhr.status === 200) {
                    resolve({
                        versionFetchCheck: true,
                        versionFetchStats: xhrReturn.status,
                        versionFetchMessage: xhrReturn.message,
                        versionFetchResponse: xhrReturn.data,
                        versionFetchError: null,
                    });
                } else {
                    reject({
                        versionFetchCheck: false,
                        versionFetchStats: xhrReturn.status,
                        versionFetchMessage:
                            xhrReturn.message || "An error occurred",
                        versionFetchResponse:
                            xhrReturn.data || "Something went wrong on our end",
                        versionFetchError:
                            xhrReturn.errors ||
                            xhrReturn.message ||
                            "Internal Server Error",
                    });
                }
            } catch (e) {
                reject({
                    versionFetchCheck: false,
                    versionFetchStats: 500,
                    versionFetchMessage: "Internal Server Error",
                    versionFetchResponse: e,
                    versioningError: `Error parsing JSON: ${e}`,
                });
            }
        };

        xhr.onerror = () => {
            reject({
                versionFetchCheck: false,
                versionFetchStats: xhr.status || 500,
                versionFetchMessage: "Network error occurred",
                versionFetchResponse: "Undefined",
                versioningError: "Network error",
            });
        };

        xhr.onreadystatechange = xhr.send();
    });
}

function fileNameFormat(fileName) {
    let trimmedFileName = fileName.trim();
    let newFileName = trimmedFileName
        .replace(/[^\w.]+/g, "_")
        .replace(/_+/g, "_");

    return newFileName;
}

function generateMesssage(subMessage) {
    var ul = document.getElementById("err-list");
    var li = document.createElement("li");
    li.appendChild(document.createTextNode(subMessage));
    ul.appendChild(li);
}

function generateThumbnail(fileName) {
    return new Promise(function (resolve, reject) {
        var xhr = new XMLHttpRequest();
        var formData = new FormData();
        formData.append("file", fileName.replace(/\s/g, "_"));
        xhr.open("POST", `${apiUrl}/api/v1/file/thumbnail`, true);
        xhr.setRequestHeader("Authorization", "Bearer " + bearerToken);
        xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        xhr.setRequestHeader("Accept", "application/json; charset=utf-8");
        xhr.onload = () => {
            try {
                const xhrReturn = JSON.parse(xhr.responseText);
                if (xhr.status === 201) {
                    resolve(xhrReturn.files);
                } else {
                    reject(
                        new Error(
                            "API response error: " + xhrReturn.errors ||
                                xhrReturn.message ||
                                "Internal Server Error"
                        )
                    );
                }
            } catch (e) {
                reject(new Error(`Error parsing JSON: ${e}`));
            }
        };

        xhr.onerror = () => {
            reject(new Error("Network error occurred"));
        };

        xhr.send(formData);
    });
}

function getTemporaryURL(fileName) {
    return new Promise(function (resolve, reject) {
        var xhr = new XMLHttpRequest();
        var formData = new FormData();
        formData.append("file", fileName.replace(/\s/g, "_"));

        xhr.open("POST", `${apiUrl}/api/v1/file/getTemporaryURL`, true);
        xhr.setRequestHeader("Authorization", "Bearer " + bearerToken);
        xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        xhr.setRequestHeader("Accept", "application/json; charset=utf-8");

        xhr.onload = () => {
            try {
                const xhrReturn = JSON.parse(xhr.responseText);
                if (xhr.status === 200) {
                    resolve({
                        temporaryURLStats: xhrReturn.status,
                        temporaryURLMessage: xhrReturn.message,
                        temporaryURL: xhrReturn.files,
                        temporaryError: null,
                    });
                } else {
                    reject({
                        temporaryURLStats: xhrReturn.status,
                        temporaryURLMessage:
                            xhrReturn.message || "An error occurred",
                        temporaryURL: null,
                        temporaryError:
                            xhrReturn.errors ||
                            xhrReturn.message ||
                            "Internal Server Error",
                    });
                }
            } catch (e) {
                reject({
                    temporaryURLStats: 500,
                    temporaryURLMessage: "Internal server error",
                    temporaryURL: null,
                    temporaryError: `Error parsing JSON: ${e}`,
                });
            }
        };

        xhr.onerror = () => {
            reject({
                temporaryURLStats: xhr.status || 500,
                temporaryURLMessage: "Network error occurred",
                temporaryURL: null,
                temporaryError: "Network error",
            });
        };

        xhr.send(formData);
    });
}

function getTotalPages(fileName) {
    return new Promise(function (resolve, reject) {
        var xhr = new XMLHttpRequest();
        var formData = new FormData();
        formData.append("fileName", fileName);

        xhr.open("POST", `${apiUrl}/api/v2/pdf/getTotalPagesPDF`, true);
        xhr.setRequestHeader("Authorization", "Bearer " + bearerToken);
        xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        xhr.setRequestHeader("Accept", "application/json; charset=utf-8");

        xhr.onload = () => {
            try {
                const xhrReturn = JSON.parse(xhr.responseText);
                if (xhr.status === 200) {
                    resolve({
                        totalPages: true,
                        totalPagesStats: xhrReturn.status,
                        totalPagesMessage: xhrReturn.data,
                        totalPagesError: null,
                    });
                } else {
                    reject({
                        totalPages: false,
                        totalPagesStats: xhrReturn.status,
                        totalPagesMessage:
                            xhrReturn.message || "An error occurred",
                        totalPagesError:
                            xhrReturn.errors ||
                            xhrReturn.message ||
                            "Internal Server Error",
                    });
                }
            } catch (e) {
                reject({
                    totalPages: false,
                    totalPagesMessage: "Internal server error",
                    totalPagesStats: 500,
                    totalPagesError: `Error parsing JSON: ${e}`,
                });
            }
        };

        xhr.onerror = () => {
            reject({
                totalPages: false,
                totalPagesMessage: "Network error occurred",
                totalPagesStats: xhr.status || 500,
                totalPagesError: "Network error",
            });
        };

        xhr.send(formData);
    });
}

function remainingBalance() {
    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.open("GET", `${apiUrl}/api/v1/ilovepdf/limit`, true);
        xhr.setRequestHeader("Authorization", `Bearer ${bearerToken}`);
        xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        xhr.setRequestHeader("Accept", "application/json; charset=utf-8");

        xhr.onload = () => {
            try {
                const xhrReturn = JSON.parse(xhr.responseText);
                if (xhr.status === 200) {
                    const hasBalance = xhrReturn.remaining > 0;
                    xhrBalance = true;
                    xhrBalanceRemaining = xhrReturn.remaining;
                    resolve({
                        xhrBalance: hasBalance,
                        xhrBalanceRemaining: xhrReturn.remaining,
                        xhrBalanceStatus: xhrReturn.status,
                        xhrBalanceResponse: hasBalance
                            ? xhrReturn.message
                            : errMessage,
                    });
                } else {
                    reject({
                        xhrBalance: false,
                        xhrBalanceRemaining: xhrReturn.remaining || 0,
                        xhrBalanceStatus: xhrReturn.status,
                        xhrBalanceResponse:
                            xhrReturn.errors ||
                            xhrReturn.message ||
                            "Internal Server Error",
                    });
                }
            } catch (e) {
                reject({
                    xhrBalance: false,
                    xhrBalanceRemaining: 0,
                    xhrBalanceStatus: 500,
                    xhrBalanceResponse: `Error parsing JSON: ${e}`,
                });
            }
        };

        xhr.onerror = () => {
            reject({
                xhrBalance: false,
                xhrBalanceRemaining: 0,
                xhrBalanceStatus: xhr.status || 500,
                xhrBalanceResponse: "Network error",
            });
        };

        xhr.send();
    });
}

function resetErrListMessage() {
    errListMessage.innerHTML = `
        <ul id="err-list"class="mt-1.5 list-disc list-inside font-bold"></ul>
    `;
}

function sendToAPI(files, proc, action) {
    return new Promise(function (resolve, reject) {
        var xhr = new XMLHttpRequest();
        const formData = handleFormDataSTA(files, proc, action);

        xhr.open("POST", apiUrl + "/api/v2/pdf/" + proc, true);
        xhr.setRequestHeader("Authorization", "Bearer " + bearerToken);
        xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        xhr.setRequestHeader("Accept", "application/json; charset=utf-8");

        xhr.onload = () => {
            handleResponseSTA(xhr, proc, resolve, reject);
        };

        xhr.onerror = () => {
            reject({
                xhrRequestCondition: false,
                xhrRequestStatus: xhr.status || 500,
                xhrRequestMessage: "Network error occurred",
                xhrRequestServerMessage: "Network error",
            });
        };

        xhr.send(formData);
    });
}

function handleFormDataSTA(files, proc, action) {
    const formData = new FormData();

    if (proc == "compress") {
        formData.append(
            "compMethod",
            document.querySelector('input[name="compMethod"]:checked').value
        );
    } else if (proc == "convert") {
        if (document.getElementById("cnvToPDF") !== null) {
            var cnvValue = "pdf";
            formData.append("extImage", false.toString());
        } else if (document.getElementById("cnvFrPDF") !== null) {
            var cnvValue = document.querySelector(
                'input[name="convertType"]:checked'
            ).value;
            var imgValue = document.getElementById("extImage");
            if (imgValue.checked) {
                formData.append("extImage", true.toString());
            } else {
                formData.append("extImage", false.toString());
            }
        }
        formData.append("convertType", cnvValue);
    } else if (proc == "split") {
        let mergePdf;
        let usedMethod;
        var customPageSplit = document.getElementById("customPageSplit").value;
        var customPageDelete =
            document.getElementById("customPageDelete").value;
        var firstPage = document.getElementById("fromPage").value;
        var lastPage = document.getElementById("toPage").value;
        if (document.getElementById("thirdRadio").checked) {
            mergePdf = document.getElementById("mergePDF").checked;
            usedMethod = "range";
        } else if (document.getElementById("fourthRadio").checked) {
            mergePdf = document.getElementById("mergePDF1").checked;
            usedMethod = "custom";
        } else if (document.getElementById("secondRadio").checked) {
            mergePdf = false;
            usedMethod = "custom";
        } else {
            mergePdf = null;
            usedMethod = null;
        }
        formData.append("action", action);
        formData.append("fromPage", firstPage);
        formData.append("toPage", lastPage);
        formData.append("mergePDF", mergePdf.toString());
        formData.append("customPageSplit", customPageSplit);
        formData.append("customPageDelete", customPageDelete);
        formData.append("usedMethod", usedMethod);
    } else if (proc == "watermark") {
        if (document.getElementById("firstRadio").checked == true) {
            let wmLayoutStyle;
            var imgFile = document.getElementById("wm_file_input").files[0];
            var wmRotation = document.getElementById(
                "watermarkImageRotation"
            ).value;
            var wmPage = document.getElementById("watermarkPageImage").value;
            var wmTransparency = document.getElementById(
                "watermarkImageTransparency"
            ).value;
            var wmMosaic = document.getElementById("isMosaicImage").checked;
            if (
                document.getElementById("wmRadioImageLayoutStyleA").checked ==
                true
            ) {
                wmLayoutStyle = document.getElementById(
                    "wmRadioImageLayoutStyleA"
                ).value;
            } else if (
                document.getElementById("wmRadioImageLayoutStyleB").checked ==
                true
            ) {
                wmLayoutStyle = document.getElementById(
                    "wmRadioImageLayoutStyleB"
                ).value;
            } else {
                wmLayoutStyle = document.getElementById(
                    "wmRadioImageLayoutStyleA"
                ).value;
            }
            formData.append("action", action);
            formData.append("imgFile", imgFile);
            formData.append("wmFontColor", "");
            formData.append("wmFontSize", "");
            formData.append("wmFontStyle", "Regular");
            formData.append("wmFontFamily", "Arial");
            formData.append("wmLayoutStyle", wmLayoutStyle);
            formData.append("wmRotation", wmRotation);
            formData.append("wmPage", wmPage);
            formData.append("wmText", "");
            formData.append("wmTransparency", wmTransparency);
            formData.append("wmMosaic", wmMosaic.toString());
        } else if (document.getElementById("secondRadio").checked == true) {
            let wmLayoutStyle;
            var wmFontColor =
                document.getElementById("watermarkFontColor").value;
            var wmFontFamily = document.getElementById(
                "watermarkFontFamily"
            ).value;
            var wmFontSize = document.getElementById("watermarkFontSize").value;
            var wmFontStyle =
                document.getElementById("watermarkFontStyle").value;
            var wmRotation = document.getElementById(
                "watermarkTextRotation"
            ).value;
            var wmPage = document.getElementById("watermarkPageText").value;
            var wmText = document.getElementById("watermarkText").value;
            var wmTransparency = document.getElementById(
                "watermarkTextTransparency"
            ).value;
            var wmMosaic = document.getElementById("isMosaicText").checked;
            if (
                document.getElementById("wmRadioLayoutStyleA").checked == true
            ) {
                wmLayoutStyle = document.getElementById(
                    "wmRadioLayoutStyleA"
                ).value;
            } else if (
                document.getElementById("wmRadioLayoutStyleB").checked == true
            ) {
                wmLayoutStyle = document.getElementById(
                    "wmRadioLayoutStyleB"
                ).value;
            } else {
                wmLayoutStyle = document.getElementById(
                    "wmRadioLayoutStyleA"
                ).value;
            }
            formData.append("action", action);
            formData.append("imgFile", "");
            formData.append("wmFontColor", wmFontColor);
            formData.append("wmFontSize", wmFontSize);
            formData.append("wmFontStyle", wmFontStyle);
            formData.append("wmFontFamily", wmFontFamily);
            formData.append("wmLayoutStyle", wmLayoutStyle);
            formData.append("wmRotation", wmRotation);
            formData.append("wmPage", wmPage);
            formData.append("wmText", wmText);
            formData.append("wmTransparency", wmTransparency);
            formData.append("wmMosaic", wmMosaic.toString());
        }
    } else if (proc == "html") {
        var urlValue = document.getElementById("urlToPDF").value;
        var urlMarginValue = document.getElementById("pageMargin").value;
        var urlSizeValue = document.getElementById("pageSize").value;
        var urlPageOrientation = document.querySelector(
            'input[name="pageOrientation"]:checked'
        ).value;
        var urlSinglePage = document.getElementById("isSinglePageText").checked;
        formData.append("urlToPDF", urlValue);
        formData.append("urlMarginValue", urlMarginValue);
        formData.append("urlSizeValue", urlSizeValue);
        formData.append("urlPageOrientationValue", urlPageOrientation);
        formData.append("urlSinglePage", urlSinglePage);
    }
    if (proc !== "html") {
        formData.append("batch", files.length > 1);
        files.forEach((file, index) => formData.append(`file[${index}]`, file));
    }

    return formData;
}

function handleResponseSTA(xhr, proc, resolve, reject) {
    try {
        const xhrReturn = JSON.parse(xhr.responseText);
        if (xhr.status === 200) {
            scsInterfaceSTA(xhrReturn, proc);
            resolve({
                xhrRequestCondition: "OK",
                xhrRequestMessage: xhrReturn.message,
                xhrRequestServerMessage: "",
                xhrRequestStatus: xhrReturn.status,
            });
        } else {
            errInterfaceSTA(xhrReturn);
            reject({
                xhrRequestCondition: "ERROR",
                xhrRequestMessage: xhrReturn.message || "An error occurred",
                xhrRequestServerMessage:
                    xhrReturn.errors ||
                    xhrReturn.message ||
                    "Internal Server Error",
                xhrRequestStatus: xhrReturn.status,
            });
        }
    } catch (e) {
        reject({
            xhrRequestCondition: "Internal Server Error",
            xhrRequestMessage: `Error parsing JSON: ${e}`,
            xhrRequestServerMessage: e,
            xhrRequestStatus: 500,
        });
    }
}

function scsInterfaceSTA(xhrReturn, proc) {
    const alertSuccess = document.getElementById("alert-scs");
    const alertError = document.getElementById("alert-err");
    alertError.classList.add("hidden", "opacity-0");
    alertSuccess.classList.remove("hidden", "opacity-0");
    document.getElementById("scsMsgTitle").innerText =
        "HANA PDF has processed your document!";
    if (proc == "compress" && xhrTotalUploads == 1) {
        document.getElementById("scsMsgResult").textContent = `
        Compressed to ${xhrReturn.newFileSize} with ${xhrReturn.compMethod} compression level.`;
    } else {
        document.getElementById(
            "scsMsgResult"
        ).innerText = `Download the file or PDF below.`;
    }
    document.getElementById("scsMsgLink").href = `${xhrReturn.fileSource}`;
    document.getElementById("scsMsgLink").innerText = "Download PDF";
}

function errInterfaceSTA(xhrReturn) {
    const alertSuccess = document.getElementById("alert-scs");
    const alertError = document.getElementById("alert-err");
    alertError.classList.remove("hidden", "opacity-0");
    alertSuccess.classList.add("hidden", "opacity-0");
    document.getElementById("errMsgTitle").innerText =
        "HANA PDF has processed your document !";
    document.getElementById("errMsg").innerText =
        xhrReturn.errors || "Internal Server Error";
    document.getElementById("errProcId").innerText = xhrReturn.groupId;
}

function submit(event) {
    if (
        document.getElementById("compress") !== null ||
        document.getElementById("cnvFrPDF") !== null
    ) {
        if (
            !document.getElementById("firstRadio").checked &&
            !document.getElementById("secondRadio").checked &&
            !document.getElementById("thirdRadio").checked
        ) {
            if (document.getElementById("cnvFrPDF") !== null) {
                if (!document.getElementById("fourthRadio").checked) {
                    var cnvImage = document.getElementById("firstRadio");
                    var cnvXls = document.getElementById("secondRadio");
                    var cnvPptx = document.getElementById("thirdRadio");
                    var cnvdocx = document.getElementById("fourthRadio");
                    event.preventDefault();
                    errMessage.innerText = "Please select out these fields!";
                    errSubMessage.innerText = "";
                    errListTitleMessage.innerText = "Required fields:";
                    errAltSubMessageModal.style = null;
                    resetErrListMessage();
                    generateMesssage("Document Format");
                    cnvImage.style.borderColor = "#A84E4E";
                    cnvXls.style.borderColor = "#A84E4E";
                    cnvPptx.style.borderColor = "#A84E4E";
                    cnvdocx.style.borderColor = "#A84E4E";
                    loadingModal.hide();
                    errModal.show();
                } else {
                    var cnvImage = document.getElementById("firstRadio");
                    var cnvPptx = document.getElementById("secondRadio");
                    var cnvXls = document.getElementById("thirdRadio");
                    var cnvDocx = document.getElementById("fourthRadio");
                    cnvImage.style.borderColor = "#4DAAAA";
                    cnvPptx.style.borderColor = "#4DAAAA";
                    cnvXls.style.borderColor = "#4DAAAA";
                    cnvDocx.style.borderColor = "#4DAAAA";
                    if (getUploadedFileName().length > 0) {
                        if (xhrBalance && xhrBalanceRemaining > 0) {
                            procTitleMessageModal.innerText =
                                "Processing document";
                            errMessage.style.visibility = null;
                            errSubMessage.style.visibility = null;
                            errAltSubMessageModal.style.display = "none";
                            errModal.hide();
                            loadingModal.show();
                            if (document.getElementById("cnvFrPDF") !== null) {
                                altLoadingMessageModal.innerText =
                                    "Processing document";
                                document
                                    .getElementById("altLoadingModal")
                                    .classList.remove("hidden");
                                document
                                    .getElementById("dropzoneCnvFrPDF")
                                    .classList.add("animate-pulse");
                                xhrProcStats = false;
                                apiGateway("convert", "");
                            } else {
                                altLoadingMessageModal.innerText =
                                    "Processing document";
                                document
                                    .getElementById("altLoadingModal")
                                    .classList.remove("hidden");
                                document
                                    .getElementById("dropzoneCmp")
                                    .classList.add("animate-pulse");
                                xhrProcStats = false;
                                apiGateway("compress", "");
                            }
                        } else {
                            event.preventDefault();
                            errMessage.innerText =
                                "Document file can not be processed !";
                            errSubMessage.innerText = "";
                            errListTitleMessage.innerText = "Error message";
                            resetErrListMessage();
                            generateMesssage(
                                "Remaining monthly limit (" +
                                    xhrBalanceRemaining +
                                    " out of 2500)"
                            );
                            errAltSubMessageModal.style = null;
                            loadingModal.hide();
                            errModal.show();
                        }
                    } else {
                        event.preventDefault();
                        errMessage.innerText =
                            "Document file can not be processed !";
                        errSubMessage.innerText = "";
                        errListTitleMessage.innerText = "Error message";
                        resetErrListMessage();
                        generateMesssage("No file has been chosen");
                        errAltSubMessageModal.style = null;
                        loadingModal.hide();
                        errModal.show();
                    }
                }
            } else {
                var compLow = document.getElementById("firstRadio");
                var compMed = document.getElementById("secondRadio");
                var compHigh = document.getElementById("thirdRadio");
                event.preventDefault();
                errMessage.innerText = "Please select out these fields!";
                errSubMessage.innerText = "";
                errListTitleMessage.innerText = "Required fields:";
                errAltSubMessageModal.style = null;
                resetErrListMessage();
                generateMesssage("Compression Quality");
                compLow.style.borderColor = "#A84E4E";
                compMed.style.borderColor = "#A84E4E";
                compHigh.style.borderColor = "#A84E4E";
                loadingModal.hide();
                errModal.show();
            }
        } else {
            var compLow = document.getElementById("firstRadio");
            var compMed = document.getElementById("secondRadio");
            var compHigh = document.getElementById("thirdRadio");
            compLow.style.borderColor = "#4DAAAA";
            compMed.style.borderColor = "#4DAAAA";
            compHigh.style.borderColor = "#4DAAAA";
            if (document.getElementById("cnvFrPDF") !== null) {
                var cnvdocx = document.getElementById("fourthRadio");
                cnvdocx.style.borderColor = "#4DAAAA";
            }
            if (getUploadedFileName().length > 0) {
                if (xhrBalance && xhrBalanceRemaining > 0) {
                    procTitleMessageModal.innerText = "Processing document";
                    errMessage.style.visibility = null;
                    errSubMessage.style.visibility = null;
                    errAltSubMessageModal.style.display = "none";
                    errModal.hide();
                    loadingModal.show();
                    if (document.getElementById("cnvFrPDF") !== null) {
                        altLoadingMessageModal.innerText =
                            "Processing document";
                        document
                            .getElementById("altLoadingModal")
                            .classList.remove("hidden");
                        document
                            .getElementById("dropzoneCnvFrPDF")
                            .classList.add("animate-pulse");
                        xhrProcStats = false;
                        apiGateway("convert", "");
                    } else {
                        altLoadingMessageModal.innerText =
                            "Processing document";
                        document
                            .getElementById("altLoadingModal")
                            .classList.remove("hidden");
                        document
                            .getElementById("dropzoneCmp")
                            .classList.add("animate-pulse");
                        xhrProcStats = false;
                        apiGateway("compress", "");
                    }
                } else {
                    event.preventDefault();
                    errMessage.innerText =
                        "Document file can not be processed !";
                    errSubMessage.innerText = "";
                    errListTitleMessage.innerText = "Error message";
                    resetErrListMessage();
                    generateMesssage(
                        "Remaining monthly limit (" +
                            xhrBalanceRemaining +
                            " out of 2500)"
                    );
                    errAltSubMessageModal.style = null;
                    loadingModal.hide();
                    errModal.show();
                }
            } else {
                event.preventDefault();
                errMessage.innerText = "Document file can not be processed !";
                errSubMessage.innerText = "";
                errListTitleMessage.innerText = "Error message";
                resetErrListMessage();
                generateMesssage("No file has been chosen");
                errAltSubMessageModal.style = null;
                loadingModal.hide();
                errModal.show();
            }
        }
    } else if (
        document.getElementById("cnvToPDF") !== null ||
        document.getElementById("merge") !== null
    ) {
        if (getUploadedFileName().length > 0) {
            if (xhrBalance && xhrBalanceRemaining > 0) {
                procTitleMessageModal.innerText = "Processing document";
                errMessage.style.visibility = null;
                errSubMessage.style.visibility = null;
                errAltSubMessageModal.style.display = "none";
                errModal.hide();
                loadingModal.show();
                if (document.getElementById("cnvToPDF") !== null) {
                    altLoadingMessageModal.innerText = "Processing document";
                    document
                        .getElementById("altLoadingModal")
                        .classList.remove("hidden");
                    document
                        .getElementById("dropzoneCnvToPDF")
                        .classList.add("animate-pulse");
                    xhrProcStats = false;
                    apiGateway("convert", "");
                } else {
                    if (getUploadedFileName().length < 2) {
                        event.preventDefault();
                        errMessage.innerText =
                            "Document file can not be processed !";
                        errSubMessage.innerText = "";
                        errListTitleMessage.innerText = "Required fields:";
                        errAltSubMessageModal.style = null;
                        resetErrListMessage();
                        generateMesssage(
                            "Minimum document to merge is 2 (Total files: " +
                                getUploadedFileName().length +
                                ")"
                        );
                        loadingModal.hide();
                        errModal.show();
                    } else {
                        altLoadingMessageModal.innerText =
                            "Processing document";
                        document
                            .getElementById("altLoadingModal")
                            .classList.remove("hidden");
                        document
                            .getElementById("dropzoneMerge")
                            .classList.add("animate-pulse");
                        xhrProcStats = false;
                        apiGateway("merge", "");
                    }
                }
            } else {
                event.preventDefault();
                errMessage.innerText = "Document file can not be processed !";
                errSubMessage.innerText = "";
                errListTitleMessage.innerText = "Error message";
                resetErrListMessage();
                generateMesssage(
                    "Remaining monthly limit (" +
                        xhrBalanceRemaining +
                        " out of 2500)"
                );
                errAltSubMessageModal.style = null;
                loadingModal.hide();
                errModal.show();
            }
        } else {
            event.preventDefault();
            errMessage.innerText = "Document file can not be processed !";
            errSubMessage.innerText = "";
            errListTitleMessage.innerText = "Error message";
            resetErrListMessage();
            generateMesssage("No file has been chosen");
            errAltSubMessageModal.style = null;
            loadingModal.hide();
            errModal.show();
        }
    } else if (document.getElementById("splitLayout1")) {
        if (getUploadedFileName().length > 0) {
            if (document.getElementById("firstRadio").checked) {
                let cusPage = false;
                let fromPage = false;
                let toPage = false;
                var customPage = document.getElementById("customPageSplit");
                var firstPage = document.getElementById("fromPage");
                var lastPage = document.getElementById("toPage");
                if (document.getElementById("thirdRadio").checked) {
                    if (document.getElementById("fromPage").value) {
                        fromPage = true;
                    } else {
                        fromPage = false;
                    }
                    if (document.getElementById("toPage").value) {
                        toPage = true;
                    } else {
                        toPage = false;
                    }

                    getTotalPages(getUploadedFileName()[0].replace(/\s/g, "_"))
                        .then((totalPages) => {
                            if (totalPages.totalPages == false) {
                                event.preventDefault();
                                errMessage.innerText =
                                    "There was unexpected error !";
                                errListTitleMessage.innerText = "Error message";
                                errAltSubMessageModal.style = null;
                                resetErrListMessage();
                                generateMesssage(totalPages.totalPagesError);
                                loadingModal.hide();
                                errModal.show();
                            } else {
                                if (fromPage && toPage) {
                                    if (
                                        document
                                            .getElementById("fromPage")
                                            .value.charAt(0) == "-"
                                    ) {
                                        event.preventDefault();
                                        errMessage.innerText =
                                            "Invalid page number range!";
                                        errListTitleMessage.innerText =
                                            "Error message";
                                        errAltSubMessageModal.style = null;
                                        resetErrListMessage();
                                        generateMesssage(
                                            "Page number can not use negative number"
                                        );
                                        firstPage.style.borderColor = "#A84E4E";
                                        loadingModal.hide();
                                        errModal.show();
                                    } else if (
                                        document
                                            .getElementById("toPage")
                                            .value.charAt(0) == "-"
                                    ) {
                                        event.preventDefault();
                                        errMessage.innerText =
                                            "Invalid page number range!";
                                        errListTitleMessage.innerText =
                                            "Error message";
                                        errAltSubMessageModal.style = null;
                                        resetErrListMessage();
                                        generateMesssage(
                                            "Page number can not use negative number"
                                        );
                                        lastPage.style.borderColor = "#A84E4E";
                                        loadingModal.hide();
                                        errModal.show();
                                    } else {
                                        if (
                                            parseInt(
                                                document.getElementById(
                                                    "fromPage"
                                                ).value
                                            ) > totalPages.totalPagesMessage
                                        ) {
                                            event.preventDefault();
                                            errMessage.innerText =
                                                "Invalid page number range!";
                                            errListTitleMessage.innerText =
                                                "Error message";
                                            errAltSubMessageModal.style = null;
                                            resetErrListMessage();
                                            generateMesssage(
                                                "First page can not be more than total page"
                                            );
                                            firstPage.style.borderColor =
                                                "#A84E4E";
                                            loadingModal.hide();
                                            errModal.show();
                                        } else if (
                                            parseInt(
                                                document.getElementById(
                                                    "toPage"
                                                ).value
                                            ) > totalPages.totalPagesMessage
                                        ) {
                                            event.preventDefault();
                                            errMessage.innerText =
                                                "Invalid page number range!";
                                            errListTitleMessage.innerText =
                                                "Error message";
                                            errAltSubMessageModal.style = null;
                                            resetErrListMessage();
                                            generateMesssage(
                                                "Last page can not be more than total page"
                                            );
                                            lastPage.style.borderColor =
                                                "#A84E4E";
                                            loadingModal.hide();
                                            errModal.show();
                                        } else {
                                            if (
                                                parseInt(
                                                    document.getElementById(
                                                        "fromPage"
                                                    ).value
                                                ) >
                                                parseInt(
                                                    document.getElementById(
                                                        "toPage"
                                                    ).value
                                                )
                                            ) {
                                                event.preventDefault();
                                                errMessage.innerText =
                                                    "Invalid page number range!";
                                                errListTitleMessage.innerText =
                                                    "Error message";
                                                errAltSubMessageModal.style =
                                                    null;
                                                resetErrListMessage();
                                                generateMesssage(
                                                    "First page can not be more than last page"
                                                );
                                                firstPage.style.borderColor =
                                                    "#A84E4E";
                                                loadingModal.hide();
                                                errModal.show();
                                            } else if (
                                                parseInt(
                                                    document.getElementById(
                                                        "toPage"
                                                    ).value
                                                ) <
                                                parseInt(
                                                    document.getElementById(
                                                        "fromPage"
                                                    ).value
                                                )
                                            ) {
                                                event.preventDefault();
                                                errMessage.innerText =
                                                    "Invalid page number range!";
                                                errListTitleMessage.innerText =
                                                    "Error message";
                                                errAltSubMessageModal.style =
                                                    null;
                                                resetErrListMessage();
                                                generateMesssage(
                                                    "Last page can not be less than first page"
                                                );
                                                firstPage.style.borderColor =
                                                    "#A84E4E";
                                                loadingModal.hide();
                                                errModal.show();
                                            }
                                            if (
                                                parseInt(
                                                    document.getElementById(
                                                        "fromPage"
                                                    ).value
                                                ) == 0 ||
                                                parseInt(
                                                    document.getElementById(
                                                        "toPage"
                                                    ).value
                                                ) == 0
                                            ) {
                                                event.preventDefault();
                                                errMessage.innerText =
                                                    "Invalid page number range!";
                                                errListTitleMessage.innerText =
                                                    "Error message";
                                                errAltSubMessageModal.style =
                                                    null;
                                                resetErrListMessage();
                                                generateMesssage(
                                                    "First or last page can't be empty or zero"
                                                );
                                                firstPage.style.borderColor =
                                                    "#A84E4E";
                                                lastPage.style.borderColor =
                                                    "#A84E4E";
                                                loadingModal.hide();
                                                errModal.show();
                                            } else {
                                                if (
                                                    xhrBalance &&
                                                    xhrBalanceRemaining > 0
                                                ) {
                                                    procTitleMessageModal.innerText =
                                                        "Processing document";
                                                    errMessage.style.visibility =
                                                        null;
                                                    errSubMessage.style.visibility =
                                                        null;
                                                    errAltSubMessageModal.style.display =
                                                        "none";
                                                    errModal.hide();
                                                    loadingModal.show();
                                                    altLoadingMessageModal.innerText =
                                                        "Processing document";
                                                    document
                                                        .getElementById(
                                                            "altLoadingModal"
                                                        )
                                                        .classList.remove(
                                                            "hidden"
                                                        );
                                                    document
                                                        .getElementById(
                                                            "dropzoneSplit"
                                                        )
                                                        .classList.add(
                                                            "animate-pulse"
                                                        );
                                                    xhrProcStats = false;
                                                    apiGateway(
                                                        "split",
                                                        "split"
                                                    );
                                                } else {
                                                    event.preventDefault();
                                                    errMessage.innerText =
                                                        "Document file can not be processed !";
                                                    errSubMessage.innerText =
                                                        "";
                                                    errListTitleMessage.innerText =
                                                        "Error message";
                                                    resetErrListMessage();
                                                    generateMesssage(
                                                        "Remaining monthly limit (" +
                                                            xhrBalanceRemaining +
                                                            " out of 2500)"
                                                    );
                                                    errAltSubMessageModal.style =
                                                        null;
                                                    loadingModal.hide();
                                                    errModal.show();
                                                }
                                            }
                                        }
                                    }
                                } else if (!fromPage && !toPage) {
                                    event.preventDefault();
                                    errMessage.innerText =
                                        "Please fill out these fields!";
                                    errSubMessage.innerText = "";
                                    errListTitleMessage.innerText =
                                        "Required fields:";
                                    errAltSubMessageModal.style = null;
                                    resetErrListMessage();
                                    generateMesssage("First Pages");
                                    generateMesssage("Last Pages");
                                    firstPage.style.borderColor = "#A84E4E";
                                    lastPage.style.borderColor = "#A84E4E";
                                    loadingModal.hide();
                                    errModal.show();
                                } else if (!fromPage && toPage) {
                                    event.preventDefault();
                                    errMessage.innerText =
                                        "Please fill out these fields!";
                                    errSubMessage.innerText = "";
                                    errListTitleMessage.innerText =
                                        "Required fields:";
                                    errAltSubMessageModal.style = null;
                                    resetErrListMessage();
                                    generateMesssage("First Pages");
                                    firstPage.style.borderColor = "#A84E4E";
                                    loadingModal.hide();
                                    errModal.show();
                                } else if (fromPage && !toPage) {
                                    event.preventDefault();
                                    errMessage.innerText =
                                        "Please fill out these fields!";
                                    errSubMessage.innerText = "";
                                    errListTitleMessage.innerText =
                                        "Required fields:";
                                    errAltSubMessageModal.style = null;
                                    resetErrListMessage();
                                    generateMesssage("Last Pages");
                                    lastPage.style.borderColor = "#A84E4E";
                                    loadingModal.hide();
                                    errModal.show();
                                } else {
                                    event.preventDefault();
                                    errMessage.innerText =
                                        "Index out of bound!";
                                    errSubMessage.innerText = "";
                                    errAltSubMessageModal.style = null;
                                    errListTitleMessage.innerText =
                                        "Error message";
                                    resetErrListMessage();
                                    generateMesssage(
                                        "Split selected page logic error"
                                    );
                                    errAltSubMessageModal.style = null;
                                    loadingModal.hide();
                                    errModal.show();
                                }
                            }
                        })
                        .catch((error) => {
                            event.preventDefault();
                            errMessage.innerText = "Invalid page number range!";
                            errListTitleMessage.innerText = "Error message";
                            errAltSubMessageModal.style = null;
                            resetErrListMessage();
                            generateMesssage(error.totalPagesError);
                            loadingModal.hide();
                            errModal.show();
                        });
                } else if (document.getElementById("fourthRadio").checked) {
                    if (document.getElementById("customPageSplit").value) {
                        cusPage = true;
                    } else {
                        cusPage = false;
                    }
                    if (cusPage) {
                        var cusPageValue =
                            document.getElementById("customPageSplit").value;
                        if (!Number.isNaN(cusPageValue)) {
                            getTotalPages(
                                getUploadedFileName()[0].replace(/\s/g, "_")
                            )
                                .then((totalPages) => {
                                    if (totalPages.totalPages == false) {
                                        event.preventDefault();
                                        errMessage.innerText =
                                            "There was unexpected error !";
                                        errListTitleMessage.innerText =
                                            "Error message";
                                        errAltSubMessageModal.style = null;
                                        resetErrListMessage();
                                        generateMesssage(
                                            totalPages.totalPagesError
                                        );
                                        loadingModal.hide();
                                        errModal.show();
                                    } else {
                                        if (
                                            parseInt(cusPageValue) >
                                            totalPages.totalPagesMessage
                                        ) {
                                            event.preventDefault();
                                            errMessage.innerText =
                                                "Invalid page number range!";
                                            errListTitleMessage.innerText =
                                                "Error message";
                                            errAltSubMessageModal.style = null;
                                            resetErrListMessage();
                                            generateMesssage(
                                                "Custom page can not be more than total page"
                                            );
                                            customPage.style.borderColor =
                                                "#A84E4E";
                                            loadingModal.hide();
                                            errModal.show();
                                        } else if (
                                            parseInt(cusPageValue) == 0
                                        ) {
                                            event.preventDefault();
                                            errMessage.innerText =
                                                "Invalid page number range!";
                                            errListTitleMessage.innerText =
                                                "Error message";
                                            errAltSubMessageModal.style = null;
                                            resetErrListMessage();
                                            generateMesssage(
                                                "Custom page can not be empty or zero"
                                            );
                                            customPage.style.borderColor =
                                                "#A84E4E";
                                            loadingModal.hide();
                                            errModal.show();
                                        } else {
                                            if (
                                                xhrBalance &&
                                                xhrBalanceRemaining > 0
                                            ) {
                                                procTitleMessageModal.innerText =
                                                    "Processing document";
                                                errMessage.style.visibility =
                                                    null;
                                                errSubMessage.style.visibility =
                                                    null;
                                                errAltSubMessageModal.style.display =
                                                    "none";
                                                errModal.hide();
                                                loadingModal.show();
                                                altLoadingMessageModal.innerText =
                                                    "Processing document";
                                                document
                                                    .getElementById(
                                                        "altLoadingModal"
                                                    )
                                                    .classList.remove("hidden");
                                                document
                                                    .getElementById(
                                                        "dropzoneSplit"
                                                    )
                                                    .classList.add(
                                                        "animate-pulse"
                                                    );
                                                xhrProcStats = false;
                                                apiGateway("split", "split");
                                            } else {
                                                event.preventDefault();
                                                errMessage.innerText =
                                                    "Document file can not be processed !";
                                                errSubMessage.innerText = "";
                                                errListTitleMessage.innerText =
                                                    "Error message";
                                                resetErrListMessage();
                                                generateMesssage(
                                                    "Remaining monthly limit (" +
                                                        xhrBalanceRemaining +
                                                        " out of 2500)"
                                                );
                                                errAltSubMessageModal.style =
                                                    null;
                                                loadingModal.hide();
                                                errModal.show();
                                            }
                                        }
                                    }
                                })
                                .catch((error) => {
                                    event.preventDefault();
                                    errMessage.innerText =
                                        "Invalid page number range!";
                                    errListTitleMessage.innerText =
                                        "Error message";
                                    errAltSubMessageModal.style = null;
                                    resetErrListMessage();
                                    generateMesssage(error.totalPagesError);
                                    loadingModal.hide();
                                    errModal.show();
                                });
                        } else {
                            procTitleMessageModal.innerText =
                                "Processing document";
                            errMessage.style.visibility = null;
                            errSubMessage.style.visibility = null;
                            errAltSubMessageModal.style.display = "none";
                            errModal.hide();
                            loadingModal.show();
                            if (xhrBalance && xhrBalanceRemaining > 0) {
                                altLoadingMessageModal.innerText =
                                    "Processing document";
                                document
                                    .getElementById("altLoadingModal")
                                    .classList.remove("hidden");
                                document
                                    .getElementById("dropzoneSplit")
                                    .classList.add("animate-pulse");
                                xhrProcStats = false;
                                apiGateway("split", "split");
                            } else {
                                event.preventDefault();
                                errMessage.innerText =
                                    "Document file can not be processed !";
                                errSubMessage.innerText = "";
                                errListTitleMessage.innerText = "Error message";
                                resetErrListMessage();
                                generateMesssage(
                                    "Remaining monthly limit (" +
                                        xhrBalanceRemaining +
                                        " out of 2500)"
                                );
                                errAltSubMessageModal.style = null;
                                loadingModal.hide();
                                errModal.show();
                            }
                        }
                    } else {
                        event.preventDefault();
                        errMessage.innerText = "Please fill out these fields!";
                        errSubMessage.innerText = "";
                        errListTitleMessage.innerText = "Required fields:";
                        errAltSubMessageModal.style = null;
                        resetErrListMessage();
                        generateMesssage("Custom Pages");
                        customPage.style.borderColor = "#A84E4E";
                        loadingModal.hide();
                        errModal.show();
                    }
                } else {
                    event.preventDefault();
                    errMessage.innerText = "Index out of bound!";
                    errSubMessage.innerText = "";
                    errListTitleMessage.innerText = "Error message";
                    resetErrListMessage();
                    generateMesssage("Cannot define selected or custom page");
                    errAltSubMessageModal.style = null;
                    loadingModal.hide();
                    errModal.show();
                }
            } else if (document.getElementById("secondRadio").checked) {
                let cusPage;
                var customPage = document.getElementById("customPageDelete");
                if (document.getElementById("customPageDelete").value) {
                    cusPage = true;
                } else {
                    cusPage = false;
                }
                if (cusPage) {
                    var cusPageValue =
                        document.getElementById("customPageDelete").value;
                    if (!Number.isNaN(cusPageValue)) {
                        getTotalPages(
                            getUploadedFileName()[0].replace(/\s/g, "_")
                        )
                            .then((totalPages) => {
                                if (totalPages.totalPages == false) {
                                    event.preventDefault();
                                    errMessage.innerText =
                                        "There was unexpected error !";
                                    errListTitleMessage.innerText =
                                        "Error message";
                                    errAltSubMessageModal.style = null;
                                    resetErrListMessage();
                                    generateMesssage(
                                        totalPages.totalPagesError
                                    );
                                    loadingModal.hide();
                                    errModal.show();
                                } else {
                                    if (
                                        parseInt(cusPageValue) >
                                        totalPages.totalPagesMessage
                                    ) {
                                        event.preventDefault();
                                        errMessage.innerText =
                                            "Invalid page number range!";
                                        errListTitleMessage.innerText =
                                            "Error message";
                                        errAltSubMessageModal.style = null;
                                        resetErrListMessage();
                                        generateMesssage(
                                            "Custom page can not be more than total page"
                                        );
                                        customPage.style.borderColor =
                                            "#A84E4E";
                                        loadingModal.hide();
                                        errModal.show();
                                    } else if (parseInt(cusPageValue) == 0) {
                                        event.preventDefault();
                                        errMessage.innerText =
                                            "Invalid page number range!";
                                        errListTitleMessage.innerText =
                                            "Error message";
                                        errAltSubMessageModal.style = null;
                                        resetErrListMessage();
                                        generateMesssage(
                                            "Custom page can not be empty or zero"
                                        );
                                        customPage.style.borderColor =
                                            "#A84E4E";
                                        loadingModal.hide();
                                        errModal.show();
                                    } else {
                                        procTitleMessageModal.innerText =
                                            "Processing document";
                                        errMessage.style.visibility = null;
                                        errSubMessage.style.visibility = null;
                                        errAltSubMessageModal.style.display =
                                            "none";
                                        errModal.hide();
                                        loadingModal.show();
                                        if (
                                            xhrBalance &&
                                            xhrBalanceRemaining > 0
                                        ) {
                                            altLoadingMessageModal.innerText =
                                                "Processing document";
                                            document
                                                .getElementById(
                                                    "altLoadingModal"
                                                )
                                                .classList.remove("hidden");
                                            document
                                                .getElementById("dropzoneSplit")
                                                .classList.add("animate-pulse");
                                            xhrProcStats = false;
                                            apiGateway("split", "delete");
                                        } else {
                                            event.preventDefault();
                                            errMessage.innerText =
                                                "Document file can not be processed !";
                                            errSubMessage.innerText = "";
                                            errListTitleMessage.innerText =
                                                "Error message";
                                            resetErrListMessage();
                                            generateMesssage(
                                                "Remaining monthly limit (" +
                                                    xhrBalanceRemaining +
                                                    " out of 2500)"
                                            );
                                            errAltSubMessageModal.style = null;
                                            loadingModal.hide();
                                            errModal.show();
                                        }
                                    }
                                }
                            })
                            .catch((error) => {
                                event.preventDefault();
                                errMessage.innerText =
                                    "Invalid page number range!";
                                errListTitleMessage.innerText = "Error message";
                                errAltSubMessageModal.style = null;
                                resetErrListMessage();
                                generateMesssage(error.totalPagesError);
                                loadingModal.hide();
                                errModal.show();
                            });
                    } else {
                        procTitleMessageModal.innerText = "Processing document";
                        errMessage.style.visibility = null;
                        errSubMessage.style.visibility = null;
                        errAltSubMessageModal.style.display = "none";
                        errModal.hide();
                        loadingModal.show();
                        if (xhrBalance && xhrBalanceRemaining > 0) {
                            altLoadingMessageModal.innerText =
                                "Processing document";
                            document
                                .getElementById("altLoadingModal")
                                .classList.remove("hidden");
                            document
                                .getElementById("dropzoneSplit")
                                .classList.add("animate-pulse");
                            xhrProcStats = false;
                            apiGateway("split", "delete");
                        } else {
                            event.preventDefault();
                            errMessage.innerText =
                                "Document file can not be processed !";
                            errSubMessage.innerText = "";
                            errListTitleMessage.innerText = "Error message";
                            resetErrListMessage();
                            generateMesssage(
                                "Remaining monthly limit (" +
                                    xhrBalanceRemaining +
                                    " out of 2500)"
                            );
                            errAltSubMessageModal.style = null;
                            loadingModal.hide();
                            errModal.show();
                        }
                    }
                } else {
                    event.preventDefault();
                    errMessage.innerText = "Please fill out these fields!";
                    errSubMessage.innerText = "";
                    errListTitleMessage.innerText = "Required fields:";
                    errAltSubMessageModal.style = null;
                    resetErrListMessage();
                    generateMesssage("Custom Pages");
                    errSubMessage.style.visibility = null;
                    customPage.style.borderColor = "#A84E4E";
                    loadingModal.hide();
                    errModal.show();
                }
            } else {
                event.preventDefault();
                errMessage.innerText = "Index out of bound!";
                errSubMessage.innerText = "";
                errListTitleMessage.innerText = "Error message";
                resetErrListMessage();
                generateMesssage("Split decision logic error");
                errAltSubMessageModal.style = null;
                loadingModal.hide();
                errModal.show();
            }
        } else {
            event.preventDefault();
            errMessage.innerText = "Document file can not be processed !";
            errSubMessage.innerText = "";
            errListTitleMessage.innerText = "Error message";
            resetErrListMessage();
            generateMesssage("No file has been chosen");
            errAltSubMessageModal.style = null;
            loadingModal.hide();
            errModal.show();
        }
    } else if (document.getElementById("wmMainLayout")) {
        if (getUploadedFileName().length > 0) {
            var wmImageSwitcher = document.getElementById("wmTypeImage");
            var wmTextSwitcher = document.getElementById("wmTypeText");
            if (document.getElementById("firstRadio").checked == true) {
                var wmImage = document.getElementById("wm_file_input");
                var customPage = document.getElementById("watermarkPageImage");
                wmImageSwitcher.checked = true;
                wmTextSwitcher.checked = false;
                if (document.getElementById("wm_file_input").value) {
                    var imgFile = document.getElementById("wm_file_input");
                    let fileSize = imgFile.files[0].size;
                    if (
                        imgFile.files[0].type == "image/jpeg" ||
                        imgFile.files[0].type == "image/png" ||
                        imgFile.files[0].type == "image/jpg"
                    ) {
                        if (fileSize >= 5242880) {
                            event.preventDefault();
                            errMessage.innerText =
                                "Uploaded file has exceeds the limit!";
                            errSubMessage.innerText = "";
                            errListTitleMessage.innerText = "Error message";
                            resetErrListMessage();
                            generateMesssage("Maximum file size 5 MB");
                            errAltSubMessageModal.style = null;
                            loadingModal.hide();
                            errModal.show();
                        } else {
                            if (
                                document.getElementById("watermarkPageImage")
                                    .value
                            ) {
                                procTitleMessageModal.innerText =
                                    "Processing document";
                                errMessage.style.visibility = null;
                                errSubMessage.style.visibility = null;
                                errAltSubMessageModal.style.display = "none";
                                errModal.hide();
                                loadingModal.show();
                                var cusPageValue =
                                    document.getElementById(
                                        "watermarkPageImage"
                                    ).value;
                                if (!Number.isNaN(cusPageValue)) {
                                    getTotalPages(
                                        getUploadedFileName()[0].replace(
                                            /\s/g,
                                            "_"
                                        )
                                    )
                                        .then((totalPages) => {
                                            if (
                                                totalPages.totalPages == false
                                            ) {
                                                event.preventDefault();
                                                errMessage.innerText =
                                                    "There was unexpected error !";
                                                errListTitleMessage.innerText =
                                                    "Error message";
                                                errAltSubMessageModal.style =
                                                    null;
                                                resetErrListMessage();
                                                generateMesssage(
                                                    totalPages.totalPagesError
                                                );
                                                loadingModal.hide();
                                                errModal.show();
                                            } else {
                                                if (
                                                    parseInt(cusPageValue) >
                                                    totalPages.totalPagesMessage
                                                ) {
                                                    event.preventDefault();
                                                    errMessage.innerText =
                                                        "Invalid page number range!";
                                                    errListTitleMessage.innerText =
                                                        "Error message";
                                                    errAltSubMessageModal.style =
                                                        null;
                                                    resetErrListMessage();
                                                    generateMesssage(
                                                        "Selected page can not be more than total page"
                                                    );
                                                    customPage.style.borderColor =
                                                        "#A84E4E";
                                                    loadingModal.hide();
                                                    errModal.show();
                                                } else if (
                                                    parseInt(cusPageValue) == 0
                                                ) {
                                                    event.preventDefault();
                                                    errMessage.innerText =
                                                        "Invalid page number range!";
                                                    errListTitleMessage.innerText =
                                                        "Error message";
                                                    errAltSubMessageModal.style =
                                                        null;
                                                    resetErrListMessage();
                                                    generateMesssage(
                                                        "Selected page can not be empty or zero"
                                                    );
                                                    customPage.style.borderColor =
                                                        "#A84E4E";
                                                    loadingModal.hide();
                                                    errModal.show();
                                                } else {
                                                    if (
                                                        xhrBalance &&
                                                        xhrBalanceRemaining > 0
                                                    ) {
                                                        altLoadingMessageModal.innerText =
                                                            "Processing document";
                                                        document
                                                            .getElementById(
                                                                "altLoadingModal"
                                                            )
                                                            .classList.remove(
                                                                "hidden"
                                                            );
                                                        document
                                                            .getElementById(
                                                                "dropzoneWatermark"
                                                            )
                                                            .classList.add(
                                                                "animate-pulse"
                                                            );
                                                        xhrProcStats = false;
                                                        apiGateway(
                                                            "watermark",
                                                            "img"
                                                        );
                                                    } else {
                                                        event.preventDefault();
                                                        errMessage.innerText =
                                                            "Document file can not be processed !";
                                                        errSubMessage.innerText =
                                                            "";
                                                        errListTitleMessage.innerText =
                                                            "Error message";
                                                        resetErrListMessage();
                                                        generateMesssage(
                                                            "Remaining monthly limit (" +
                                                                xhrBalanceRemaining +
                                                                " out of 2500)"
                                                        );
                                                        errAltSubMessageModal.style =
                                                            null;
                                                        loadingModal.hide();
                                                        errModal.show();
                                                    }
                                                }
                                            }
                                        })
                                        .catch((error) => {
                                            event.preventDefault();
                                            errMessage.innerText =
                                                "Invalid page number range!";
                                            errListTitleMessage.innerText =
                                                "Error message";
                                            errAltSubMessageModal.style = null;
                                            resetErrListMessage();
                                            generateMesssage(
                                                error.totalPagesError
                                            );
                                            generateMesssage(error);
                                            loadingModal.hide();
                                            errModal.show();
                                        });
                                } else {
                                    if (xhrBalance && xhrBalanceRemaining > 0) {
                                        altLoadingMessageModal.innerText =
                                            "Processing document";
                                        document
                                            .getElementById("altLoadingModal")
                                            .classList.remove("hidden");
                                        document
                                            .getElementById("dropzoneWatermark")
                                            .classList.add("animate-pulse");
                                        xhrProcStats = false;
                                        apiGateway("watermark", "img");
                                    } else {
                                        event.preventDefault();
                                        errMessage.innerText =
                                            "Document file can not be processed !";
                                        errSubMessage.innerText = "";
                                        errListTitleMessage.innerText =
                                            "Error message";
                                        resetErrListMessage();
                                        generateMesssage(
                                            "Remaining monthly limit (" +
                                                xhrBalanceRemaining +
                                                " out of 2500)"
                                        );
                                        errAltSubMessageModal.style = null;
                                        loadingModal.hide();
                                        errModal.show();
                                    }
                                }
                            } else {
                                var wmPage =
                                    document.getElementById(
                                        "watermarkPageImage"
                                    );
                                event.preventDefault();
                                errMessage.innerText =
                                    "Please fill out these fields!";
                                errSubMessage.innerText = "";
                                errListTitleMessage.innerText =
                                    "Required fields:";
                                resetErrListMessage();
                                generateMesssage("Pages");
                                errAltSubMessageModal.style = null;
                                wmPage.style.borderColor = "#A84E4E";
                                loadingModal.hide();
                                errModal.show();
                            }
                        }
                    } else {
                        event.preventDefault();
                        errMessage.innerText = "Unsupported file format!";
                        errSubMessage.innerText = "";
                        errListTitleMessage.innerText = "Error message";
                        resetErrListMessage();
                        generateMesssage(
                            "Supported file format: JPG, PNG, JPEG"
                        );
                        errAltSubMessageModal.style = null;
                        loadingModal.hide();
                        errModal.show();
                    }
                } else {
                    event.preventDefault();
                    errMessage.innerText = "Please fill out these fields!";
                    errSubMessage.innerText = "";
                    errListTitleMessage.innerText = "Required fields:";
                    resetErrListMessage();
                    generateMesssage("Image");
                    errAltSubMessageModal.style = null;
                    wmImage.style.borderColor = "#A84E4E";
                    loadingModal.hide();
                    errModal.show();
                }
            } else if (document.getElementById("secondRadio").checked == true) {
                var wmText = document.getElementById("watermarkText");
                var customPage = document.getElementById("watermarkPageText");
                wmImageSwitcher.checked = false;
                wmTextSwitcher.checked = true;
                if (
                    !document.getElementById("watermarkText").value &&
                    !document.getElementById("watermarkPageText").value
                ) {
                    var wmPage = document.getElementById("watermarkPageText");
                    event.preventDefault();
                    errMessage.innerText = "Please fill out these fields!";
                    errSubMessage.innerText = "";
                    errListTitleMessage.innerText = "Required fields:";
                    resetErrListMessage();
                    generateMesssage("Pages");
                    generateMesssage("Text");
                    errAltSubMessageModal.style = null;
                    wmText.style.borderColor = "#A84E4E";
                    wmPage.style.borderColor = "#A84E4E";
                    loadingModal.hide();
                    errModal.show();
                } else if (document.getElementById("watermarkText").value) {
                    if (document.getElementById("watermarkPageText").value) {
                        procTitleMessageModal.innerText = "Processing document";
                        errMessage.style.visibility = null;
                        errSubMessage.style.visibility = null;
                        errAltSubMessageModal.style.display = "none";
                        errModal.hide();
                        loadingModal.show();
                        var cusPageValue =
                            document.getElementById("watermarkPageText").value;
                        if (!Number.isNaN(cusPageValue)) {
                            getTotalPages(
                                getUploadedFileName()[0].replace(/\s/g, "_")
                            )
                                .then((totalPages) => {
                                    if (totalPages.totalPages == false) {
                                        event.preventDefault();
                                        errMessage.innerText =
                                            "There was unexpected error !";
                                        errListTitleMessage.innerText =
                                            "Error message";
                                        errAltSubMessageModal.style = null;
                                        resetErrListMessage();
                                        generateMesssage(
                                            totalPages.totalPagesError
                                        );
                                        loadingModal.hide();
                                        errModal.show();
                                    } else {
                                        if (
                                            parseInt(cusPageValue) >
                                            totalPages.totalPagesMessage
                                        ) {
                                            event.preventDefault();
                                            errMessage.innerText =
                                                "Invalid page number range!";
                                            errListTitleMessage.innerText =
                                                "Error message";
                                            errAltSubMessageModal.style = null;
                                            resetErrListMessage();
                                            generateMesssage(
                                                "Selected page can not be more than total page"
                                            );
                                            customPage.style.borderColor =
                                                "#A84E4E";
                                            loadingModal.hide();
                                            errModal.show();
                                        } else if (
                                            parseInt(cusPageValue) == 0
                                        ) {
                                            event.preventDefault();
                                            errMessage.innerText =
                                                "Invalid page number range!";
                                            errListTitleMessage.innerText =
                                                "Error message";
                                            errAltSubMessageModal.style = null;
                                            resetErrListMessage();
                                            generateMesssage(
                                                "Selected page can not be empty or zero"
                                            );
                                            customPage.style.borderColor =
                                                "#A84E4E";
                                            loadingModal.hide();
                                            errModal.show();
                                        } else {
                                            if (
                                                xhrBalance &&
                                                xhrBalanceRemaining > 0
                                            ) {
                                                altLoadingMessageModal.innerText =
                                                    "Processing document";
                                                document
                                                    .getElementById(
                                                        "altLoadingModal"
                                                    )
                                                    .classList.remove("hidden");
                                                document
                                                    .getElementById(
                                                        "dropzoneWatermark"
                                                    )
                                                    .classList.add(
                                                        "animate-pulse"
                                                    );
                                                xhrProcStats = false;
                                                apiGateway("watermark", "txt");
                                            } else {
                                                event.preventDefault();
                                                errMessage.innerText =
                                                    "Document file can not be processed !";
                                                errSubMessage.innerText = "";
                                                errListTitleMessage.innerText =
                                                    "Error message";
                                                resetErrListMessage();
                                                generateMesssage(
                                                    "Remaining monthly limit (" +
                                                        xhrBalanceRemaining +
                                                        " out of 2500)"
                                                );
                                                errAltSubMessageModal.style =
                                                    null;
                                                loadingModal.hide();
                                                errModal.show();
                                            }
                                        }
                                    }
                                })
                                .catch((error) => {
                                    event.preventDefault();
                                    errMessage.innerText =
                                        "Invalid page number range!";
                                    errListTitleMessage.innerText =
                                        "Error message";
                                    errAltSubMessageModal.style = null;
                                    resetErrListMessage();
                                    generateMesssage(error.totalPagesError);
                                    loadingModal.hide();
                                    errModal.show();
                                });
                        } else {
                            if (xhrBalance && xhrBalanceRemaining > 0) {
                                altLoadingMessageModal.innerText =
                                    "Processing document";
                                document
                                    .getElementById("altLoadingModal")
                                    .classList.remove("hidden");
                                document
                                    .getElementById("dropzoneWatermark")
                                    .classList.add("animate-pulse");
                                xhrProcStats = false;
                                apiGateway("watermark", "txt");
                            } else {
                                event.preventDefault();
                                errMessage.innerText =
                                    "Document file can not be processed !";
                                errSubMessage.innerText = "";
                                errListTitleMessage.innerText = "Error message";
                                resetErrListMessage();
                                generateMesssage(
                                    "Remaining monthly limit (" +
                                        xhrBalanceRemaining +
                                        " out of 2500)"
                                );
                                errAltSubMessageModal.style = null;
                                loadingModal.hide();
                                errModal.show();
                            }
                        }
                    } else {
                        var wmPage =
                            document.getElementById("watermarkPageText");
                        event.preventDefault();
                        errMessage.innerText = "Please fill out these fields!";
                        errSubMessage.innerText = "";
                        errListTitleMessage.innerText = "Required fields:";
                        resetErrListMessage();
                        generateMesssage("Pages");
                        errAltSubMessageModal.style = null;
                        wmPage.style.borderColor = "#A84E4E";
                        loadingModal.hide();
                        errModal.show();
                    }
                } else {
                    event.preventDefault();
                    errMessage.innerText = "Please fill out these fields!";
                    errSubMessage.innerText = "";
                    errListTitleMessage.innerText = "Required fields:";
                    resetErrListMessage();
                    generateMesssage("Text");
                    errAltSubMessageModal.style = null;
                    wmText.style.borderColor = "#A84E4E";
                    loadingModal.hide();
                    errModal.show();
                }
            } else {
                event.preventDefault();
                errMessage.innerText = "Please choose watermark options!";
                errSubMessage.innerText = "";
                errSubMessage.style.visibility = null;
                errAltSubMessageModal.style.display = "none";
                loadingModal.hide();
                errModal.show();
            }
        } else {
            event.preventDefault();
            errMessage.innerText = "Document file can not be processed !";
            errSubMessage.innerText = "";
            errListTitleMessage.innerText = "Error message";
            resetErrListMessage();
            generateMesssage("No file has been chosen");
            errAltSubMessageModal.style = null;
            loadingModal.hide();
            errModal.show();
        }
    } else if (document.getElementById("html") !== null) {
        var urlAddr = document.getElementById("html");
        if (document.getElementById("urlToPDF").value) {
            if (
                document.getElementById("firstRadio").checked ||
                document.getElementById("secondRadio").checked
            ) {
                if (xhrBalance && xhrBalanceRemaining > 0) {
                    procTitleMessageModal.innerText = "Processing URL";
                    errMessage.style.visibility = null;
                    errSubMessage.style.visibility = null;
                    errAltSubMessageModal.style.display = "none";
                    errModal.hide();
                    loadingModal.show();
                    altLoadingMessageModal.innerText = "Processing URL";
                    document
                        .getElementById("altLoadingModal")
                        .classList.remove("hidden");
                    document
                        .getElementById("formHTML")
                        .classList.add("animate-pulse");
                    xhrProcStats = false;
                    apiGateway("html", "");
                } else {
                    event.preventDefault();
                    errMessage.innerText =
                        "Document file can not be processed !";
                    errSubMessage.innerText = "";
                    errListTitleMessage.innerText = "Error message";
                    resetErrListMessage();
                    generateMesssage(
                        "Remaining monthly limit (" +
                            xhrBalanceRemaining +
                            " out of 2500)"
                    );
                    errAltSubMessageModal.style = null;
                    loadingModal.hide();
                    errModal.show();
                }
            } else {
                var urlLandscape = document.getElementById("firstRadio");
                var urlPotrait = document.getElementById("secondRadio");
                event.preventDefault();
                errMessage.innerText = "Please select out these fields!";
                errSubMessage.innerText = "";
                errListTitleMessage.innerText = "Required fields:";
                errAltSubMessageModal.style = null;
                resetErrListMessage();
                generateMesssage("Page Orientation");
                urlLandscape.style.borderColor = "#A84E4E";
                urlPotrait.style.borderColor = "#A84E4E";
                loadingModal.hide();
                errModal.show();
            }
        } else {
            event.preventDefault();
            errMessage.innerText = "Please fill out these fields!";
            errSubMessage.innerText = "";
            errListTitleMessage.innerText = "Required fields:";
            resetErrListMessage();
            generateMesssage("URL Address");
            errAltSubMessageModal.style = null;
            urlAddr.style.borderColor = "#A84E4E";
            loadingModal.hide();
            errModal.show();
        }
    }
}

function validateVersion() {
    return new Promise(function (resolve, reject) {
        var xhr = new XMLHttpRequest();
        var formData = new FormData();
        formData.append("appMajorVersion", appMajorVer);
        formData.append("appMinorVersion", appMinorVer);
        formData.append("appPatchVersion", appPatchVer);
        formData.append("appServicesReferrer", "FE");

        xhr.open("POST", `${apiUrl}/api/v1/version/check`, true);
        xhr.setRequestHeader("Authorization", "Bearer " + bearerToken);
        xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        xhr.setRequestHeader("Accept", "application/json; charset=utf-8");

        xhr.onload = () => {
            try {
                const xhrReturn = JSON.parse(xhr.responseText);
                if (xhr.status === 200) {
                    resolve({
                        versioningCheck: true,
                        versioningStats: xhrReturn.status,
                        versioningMessage: xhrReturn.message,
                        versioningError: null,
                    });
                } else {
                    reject({
                        versioningCheck: false,
                        versioningStats: xhrReturn.status,
                        versioningMessage:
                            xhrReturn.message || "An error occurred",
                        versioningError:
                            xhrReturn.errors ||
                            xhrReturn.message ||
                            "Internal Server Error",
                    });
                }
            } catch (e) {
                reject({
                    versioningCheck: false,
                    versioningStats: 500,
                    versioningMessage: "Internal Server Error",
                    versioningError: `Error parsing JSON: ${e}`,
                });
            }
        };

        xhr.onerror = () => {
            reject({
                versioningCheck: false,
                versioningStats: xhr.status || 500,
                versioningMessage: "Network error occurred",
                versioningError: "Network error",
            });
        };

        xhr.send(formData);
    });
}
