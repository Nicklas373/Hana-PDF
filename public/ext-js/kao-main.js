if (firstArea) {
    firstArea.onclick = function () {
        firstAreaText.style.color = "#E0E4E5";
        firstColumnArea.style.borderColor = "#4DAAAA";
        firstRadio.style.borderColor = "#4DAAAA";
        firstAreaInput.checked = true;
        secondAreaText.style.color = "#E0E4E5";
        secondColumnArea.style = null;
        if (thirdArea) {
            if (firstAreaAltInput.value !== "split") {
                thirdAreaText.style.color = "#E0E4E5";
                thirdAreaInput.checked = false;
                thirdColumnArea.style = null;
            } else {
                if (thirdAreaInput.checked == true) {
                    thirdRadio.style.borderColor = "#4DAAAA";
                    thirdColumnArea.style.borderColor = "#4DAAAA";
                    thirdAreaText.style.color = "#E0E4E5";
                    splitLayout3_wthn();
                }
            }
        }
        if (fourthArea) {
            if (firstAreaAltInput.value !== "split") {
                fourthAreaText.style.color = "#E0E4E5";
                fourthAreaInput.checked = false;
                fourthColumnArea.style = null;
            } else {
                if (fourthAreaInput.checked == true) {
                    fourthRadio.style.borderColor = "#4DAAAA";
                    fourthColumnArea.style.borderColor = "#4DAAAA";
                    fourthAreaText.style.color = "#E0E4E5";
                    splitLayout3_cstm();
                    document.getElementById("submitBtn").textContent =
                        "Delete Page";
                }
            }
        }
        if (firstAreaAltInput.value == "split") {
            splitLayout2_split();
            if (
                thirdColumnArea.style.borderColor !== "rgb(77, 170, 170)" ||
                fourthColumnArea.style.borderColor !== "rgb(77, 170, 170)"
            ) {
                splitLayout2_splitClean();
            }
        } else if (firstAreaAltInput.value == "cnvFrPDF") {
            extImageChkBox.style.display = null;
        } else if (firstAreaAltInput.value == "watermark") {
            wmLayout_image();
            showVal(
                document.getElementById("watermarkImageTransparency").value,
                "image"
            );
            document.getElementById("submitBtn").style = null;
        }
    };
}

if (secondArea) {
    secondArea.onclick = function () {
        secondAreaText.style.color = "#E0E4E5";
        secondColumnArea.style.borderColor = "#4DAAAA";
        secondRadio.style.borderColor = "#4DAAAA";
        secondAreaInput.checked = true;
        if (
            firstAreaAltInput.value == "comp" ||
            firstAreaAltInput.value == "cnvFrPDF"
        ) {
            if (firstAreaAltInput.value == "cnvFrPDF") {
                extImageChkBox.style.display = "none";
                extImageSwitch.checked = false;
            }
        }
        firstAreaText.style.color = "#E0E4E5";
        firstColumnArea.style = null;
        if (thirdArea) {
            thirdAreaText.style.color = "#E0E4E5";
            thirdColumnArea.style = null;
        }
        if (fourthArea) {
            fourthAreaText.style.color = "#E0E4E5";
            fourthColumnArea.style = null;
        }
        if (secondAreaAltInput.value == "split") {
            splitLayout2_delete();
        } else if (firstAreaAltInput.value == "watermark") {
            wmLayout_text();
            showVal(
                document.getElementById("watermarkTextTransparency").value,
                "text"
            );
            document.getElementById("submitBtn").style = null;
        }
    };
}

if (thirdArea) {
    thirdArea.onclick = function () {
        thirdAreaText.style.color = "#E0E4E5";
        thirdColumnArea.style.borderColor = "#4DAAAA";
        thirdRadio.style.borderColor = "#4DAAAA";
        thirdAreaInput.checked = true;
        if (
            firstAreaAltInput.value == "comp" ||
            firstAreaAltInput.value == "cnvFrPDF"
        ) {
            if (firstAreaAltInput.value == "cnvFrPDF") {
                extImageChkBox.style.display = "none";
                extImageSwitch.checked = false;
            }
        }
        firstAreaText.style.color = "#E0E4E5";
        firstColumnArea.style = null;
        secondAreaText.style.color = "#E0E4E5";
        secondColumnArea.style = null;
        if (fourthArea) {
            fourthAreaText.style.color = "#E0E4E5";
            fourthColumnArea.style = null;
        }
        if (thirdAreaAltInput.value == "split") {
            splitLayout3_wthn();
            if (firstAreaInput.checked == true) {
                firstAreaText.style.color = "#E0E4E5";
                firstColumnArea.style.borderColor = "#4DAAAA";
                firstRadio.style.borderColor = "#4DAAAA";
            }
        }
    };
}

if (fourthArea) {
    fourthArea.onclick = function () {
        fourthAreaText.style.color = "#E0E4E5";
        fourthColumnArea.style.borderColor = "#4DAAAA";
        fourthRadio.style.borderColor = "#4DAAAA";
        fourthAreaInput.checked = true;
        if (
            firstAreaAltInput.value == "comp" ||
            firstAreaAltInput.value == "cnvFrPDF"
        ) {
            if (firstAreaAltInput.value == "cnvFrPDF") {
                extImageChkBox.style.display = "none";
                extImageSwitch.checked = false;
            }
        }
        firstAreaText.style.color = "#E0E4E5";
        firstColumnArea.style = null;
        secondAreaText.style.color = "#E0E4E5";
        secondColumnArea.style = null;
        if (thirdArea) {
            thirdAreaText.style.color = "#E0E4E5";
            thirdColumnArea.style = null;
        }
        if (fourthAreaAltInput.value == "split") {
            splitLayout3_cstm();
            if (firstAreaInput.checked == true) {
                firstAreaText.style.color = "#E0E4E5";
                firstColumnArea.style.borderColor = "#4DAAAA";
                firstRadio.style.borderColor = "#4DAAAA";
            }
        }
    };
}

if (wmLayoutImageStyleAreaA) {
    wmLayoutImageStyleAreaA.onclick = function () {
        reuseOnClickWmLayoutImageStyleAreaA();
        if (firstAreaInput.checked == true) {
            firstAreaText.style.color = "#E0E4E5";
            firstColumnArea.style.borderColor = "#4DAAAA";
            firstRadio.style.borderColor = "#4DAAAA";
            firstAreaInput.checked = true;
        } else if (secondAreaInput.checked == true) {
            secondAreaText.style.color = "#E0E4E5";
            secondColumnArea.style.borderColor = "#4DAAAA";
            secondRadio.style.borderColor = "#4DAAAA";
            secondAreaInput.checked = true;
        }
    };
}

if (wmLayoutImageStyleAreaB) {
    wmLayoutImageStyleAreaB.onclick = function () {
        reuseOnClickWmLayoutImageStyleAreaB();
        if (firstAreaInput.checked == true) {
            firstAreaText.style.color = "#E0E4E5";
            firstColumnArea.style.borderColor = "#4DAAAA";
            firstRadio.style.borderColor = "#4DAAAA";
            firstAreaInput.checked = true;
        } else if (secondAreaInput.checked == true) {
            secondAreaText.style.color = "#E0E4E5";
            secondColumnArea.style.borderColor = "#4DAAAA";
            secondRadio.style.borderColor = "#4DAAAA";
            secondAreaInput.checked = true;
        }
    };
}

if (wmChkLayoutStyleA) {
    wmChkLayoutStyleA.onclick = function () {
        reuseOnClickWmLayoutStyleAreaA();
        if (firstAreaInput.checked == true) {
            firstAreaText.style.color = "#E0E4E5";
            firstColumnArea.style.borderColor = "#4DAAAA";
            firstRadio.style.borderColor = "#4DAAAA";
            firstAreaInput.checked = true;
        } else if (secondAreaInput.checked == true) {
            secondAreaText.style.color = "#E0E4E5";
            secondColumnArea.style.borderColor = "#4DAAAA";
            secondRadio.style.borderColor = "#4DAAAA";
            secondAreaInput.checked = true;
        }
    };
}

if (wmChkLayoutStyleB) {
    wmChkLayoutStyleB.onclick = function () {
        reuseOnClickWmLayoutStyleAreaB();
        if (firstAreaInput.checked == true) {
            firstAreaText.style.color = "#E0E4E5";
            firstColumnArea.style.borderColor = "#4DAAAA";
            firstRadio.style.borderColor = "#4DAAAA";
            firstAreaInput.checked = true;
        } else if (secondAreaInput.checked == true) {
            secondAreaText.style.color = "#E0E4E5";
            secondColumnArea.style.borderColor = "#4DAAAA";
            secondRadio.style.borderColor = "#4DAAAA";
            secondAreaInput.checked = true;
        }
    };
}

if (isImageMosaicArea) {
    isImageMosaicArea.onclick = function () {
        if (isImageMosaicCheck.checked == true) {
            isImageMosaicCheck.checked = false;
        } else {
            isImageMosaicCheck.checked = true;
        }
    };
}

if (isTextMosaicArea) {
    isTextMosaicArea.onclick = function () {
        if (isTextMosaicCheck.checked == true) {
            isTextMosaicCheck.checked = false;
        } else {
            isTextMosaicCheck.checked = true;
        }
    };
}

function checkValidation(validation) {
    if (validation == "extCustomPage" || validation == "splitCustomPage") {
        if (document.getElementById("customPageSplit").value != "") {
            document.getElementById("customPageSplit").style.borderColor =
                "#E0E4E5";
        } else {
            document.getElementById("customPageSplit").style.borderColor =
                "#992E2E";
        }
        if (document.getElementById("customPageDelete").value != "") {
            document.getElementById("customPageDelete").style.borderColor =
                "#E0E4E5";
        } else {
            document.getElementById("customPageDelete").style.borderColor =
                "#992E2E";
        }
    }
    if (validation == "splitFirstPage") {
        if (document.getElementById("fromPage").value != "") {
            document.getElementById("fromPage").style.borderColor = "#E0E4E5";
        } else {
            document.getElementById("fromPage").style.borderColor = "#992E2E";
        }
    }
    if (validation == "splitLastPage") {
        if (document.getElementById("toPage").value != "") {
            document.getElementById("toPage").style.borderColor = "#E0E4E5";
        } else {
            document.getElementById("toPage").style.borderColor = "#992E2E";
        }
    }
    if (validation == "watermarkText") {
        if (document.getElementById("watermarkText").value != "") {
            document.getElementById("watermarkText").style.borderColor =
                "#E0E4E5";
        } else {
            document.getElementById("watermarkText").style.borderColor =
                "#992E2E";
        }
        if (document.getElementById("watermarkFontSize").value != "") {
            document.getElementById("watermarkFontSize").style.borderColor =
                "#E0E4E5";
        } else {
            document.getElementById("watermarkFontSize").style.borderColor =
                "#992E2E";
        }
    }
    if (validation == "watermarkPage") {
        if (firstAreaInput.checked == true) {
            if (document.getElementById("watermarkPageImage").value != "") {
                document.getElementById(
                    "watermarkPageImage"
                ).style.borderColor = "#E0E4E5";
            } else {
                document.getElementById(
                    "watermarkPageImage"
                ).style.borderColor = "#992E2E";
            }
        } else if (secondAreaInput.checked == true) {
            if (document.getElementById("watermarkPageText").value != "") {
                document.getElementById("watermarkPageText").style.borderColor =
                    "#E0E4E5";
            } else {
                document.getElementById("watermarkPageText").style.borderColor =
                    "#992E2E";
            }
        }
    }
    if (validation == "wm_file_input") {
        if (document.getElementById("wm_file_input").value != "") {
            document.getElementById("wm_file_input").style.borderColor =
                "#E0E4E5";
        } else {
            document.getElementById("wm_file_input").style.borderColor =
                "#992E2E";
        }
    }
    if (validation == "urlToPDF") {
        if (document.getElementById("urlToPDF").value != "") {
            document.getElementById("urlToPDF").style.borderColor = "#E0E4E5";
            document.getElementById("submitBtn").style.backgroundColor =
                "#4DAAAA";
            document.getElementById("submitBtn").style.color = "white";
        } else {
            document.getElementById("urlToPDF").style.borderColor = "#992E2E";
            document.getElementById("submitBtn").style.backgroundColor = null;
            document.getElementById("submitBtn").style.color = null;
        }
    }
}

function dropdownCnvToPDF() {
    if (document.getElementById("cnvToPDFdropdown").value == "1") {
        document.getElementById("cnvToPDFdropdownImage").style.transform =
            "rotate(-90deg)";
        document.getElementById("cnvToPDFdropdown").value = "0";
    } else {
        if (document.getElementById("cnvFromPDFdropdown").value == "1") {
            document.getElementById("cnvFromPDFdropdownImage").style.transform =
                "rotate(-90deg)";
            document.getElementById("cnvFromPDFdropdown").value = "0";
        }
        document.getElementById("cnvToPDFdropdownImage").style.transform =
            "rotate(0deg)";
        document.getElementById("cnvToPDFdropdown").value = "1";
    }
}

function dropdownCnvFromPDF() {
    if (document.getElementById("cnvFromPDFdropdown").value == "1") {
        document.getElementById("cnvFromPDFdropdownImage").style.transform =
            "rotate(-90deg)";
        document.getElementById("cnvFromPDFdropdown").value = "0";
    } else {
        if (document.getElementById("cnvToPDFdropdown").value == "1") {
            document.getElementById("cnvToPDFdropdownImage").style.transform =
                "rotate(-90deg)";
            document.getElementById("cnvToPDFdropdown").value = "0";
        }
        document.getElementById("cnvFromPDFdropdownImage").style.transform =
            "rotate(0deg)";
        document.getElementById("cnvFromPDFdropdown").value = "1";
    }
}

function dropdownManage() {
    if (document.getElementById("dropdownNavbarLink").value == "1") {
        document.getElementById("dropdownNavbarImage").style.transform =
            "rotate(-90deg)";
        document.getElementById("dropdownNavbarLink").value = "0";
    } else {
        document.getElementById("dropdownNavbarImage").style.transform =
            "rotate(0deg)";
        document.getElementById("dropdownNavbarLink").value = "1";
    }
}

function fontColorValue() {
    watermarkFontColor.value = wmFontColorPicker.value;
}

function reloadIFrame() {
    var iframe = document.getElementById("iFrame");
    if (iframe !== null) {
        if (iframe.contentDocument !== null) {
            document.getElementById("iFrame").style.display = "none";
            document.getElementById("iFrameBorder").style.display = null;
        } else {
            clearInterval(timerId);
            document.getElementById("iFrameBorder").style.display = "none";
            document.getElementById("iFrame").style.display = null;
        }
    }
}

function reuseOnClickWmLayoutImageStyleAreaA() {
    wmLayoutImageStyleColumnAreaA.style.borderColor = "#4DAAAA";
    wmLayoutImageStyleAreaTextA.style.color = "#E0E4E5";
    wmLayoutImageRadioAreaInputA.checked = true;
    firstColumnArea.style = null;
    firstAreaText.color = "#E0E4E5";
    secondColumnArea.style = null;
    secondAreaText.color = "#E0E4E5";
    wmLayoutImageStyleColumnAreaB.style = null;
    wmLayoutImageStyleAreaTextB.style.color = "#E0E4E5";
    if (firstAreaInput.checked == true) {
        firstColumnArea.style.borderColor = "#4DAAAA";
        firstAreaText.style.color = "#E0E4E5";
        firstAreaInput.checked = true;
    } else if (secondAreaInput.checked == true) {
        secondColumnArea.style.borderColor = "#4DAAAA";
        secondAreaText.style.color = "#E0E4E5";
        secondAreaInput.checked = true;
    }
}

function reuseOnClickWmLayoutImageStyleAreaB() {
    wmLayoutImageStyleColumnAreaB.style.borderColor = "#4DAAAA";
    wmLayoutImageStyleAreaTextB.style.color = "#E0E4E5";
    wmLayoutImageRadioAreaInputB.checked = true;
    firstColumnArea.style = null;
    firstAreaText.color = "#E0E4E5";
    secondColumnArea.style = null;
    secondAreaText.color = "#E0E4E5";
    wmLayoutImageStyleColumnAreaA.style = null;
    wmLayoutImageStyleAreaTextA.style.color = "#E0E4E5";
    if (firstAreaInput.checked == true) {
        firstColumnArea.style.borderColor = "#4DAAAA";
        firstAreaText.style.color = "#E0E4E5";
        firstAreaInput.checked = true;
    } else if (secondAreaInput.checked == true) {
        secondColumnArea.style.borderColor = "#4DAAAA";
        secondAreaText.style.color = "#E0E4E5";
        secondAreaInput.checked = true;
    }
}

function reuseOnClickWmLayoutStyleAreaA() {
    wmColLayoutStyleA.style.borderColor = "#4DAAAA";
    wmRadioLayoutStyleTextA.style.color = "#E0E4E5";
    wmRadioLayoutStyleA.checked = true;
    firstColumnArea.style = null;
    firstAreaText.color = "#E0E4E5";
    secondColumnArea.style = null;
    secondAreaText.color = "#E0E4E5";
    wmColLayoutStyleB.style = null;
    wmRadioLayoutStyleTextB.style.color = "#E0E4E5";
    if (firstAreaInput.checked == true) {
        firstColumnArea.style.borderColor = "#4DAAAA";
        firstAreaText.style.color = "#E0E4E5";
        firstAreaInput.checked = true;
    } else if (secondAreaInput.checked == true) {
        secondColumnArea.style.borderColor = "#4DAAAA";
        secondAreaText.style.color = "#E0E4E5";
        secondAreaInput.checked = true;
    }
}

function reuseOnClickWmLayoutStyleAreaB() {
    wmColLayoutStyleB.style.borderColor = "#4DAAAA";
    wmRadioLayoutStyleTextB.style.color = "#E0E4E5";
    wmRadioLayoutStyleB.checked = true;
    firstColumnArea.style = null;
    firstAreaText.color = "#E0E4E5";
    secondColumnArea.style = null;
    secondAreaText.color = "#E0E4E5";
    wmColLayoutStyleA.style = null;
    wmRadioLayoutStyleTextA.style.color = "#E0E4E5";
    if (firstAreaInput.checked == true) {
        firstColumnArea.style.borderColor = "#4DAAAA";
        firstAreaText.style.color = "#E0E4E5";
        firstAreaInput.checked = true;
    } else if (secondAreaInput.checked == true) {
        secondColumnArea.style.borderColor = "#4DAAAA";
        secondAreaText.style.color = "#E0E4E5";
        secondAreaInput.checked = true;
    }
}

function showVal(newVal, state) {
    if (state == "text") {
        document.getElementById("TransparencyValueText").innerText =
            newVal + " %";
    } else if (state == "image") {
        document.getElementById("TransparencyValueImage").innerText =
            newVal + " %";
    } else if (state == "html") {
        document.getElementById("pageMarginValueText").innerText =
            newVal + " px";
    }
}

function splitLayout2_split() {
    document.getElementById("splitLayout2_split").style.display = null;
    document.getElementById("splitLayout2_delete").style.display = "none";
}

function splitLayout2_splitClean() {
    document.getElementById("splitLayout3Cstm").style.display = "none";
    document.getElementById("splitLayout3Wthn").style.display = "none";
}

function splitLayout2_delete() {
    document.getElementById("splitLayout2_split").style.display = "none";
    document.getElementById("splitLayout2_delete").style.display = null;
    document.getElementById("splitLayout3Cstm").style.display = "none";
    document.getElementById("splitLayout3Wthn").style.display = "none";
    document.getElementById("submitBtn").style.display = null;
}

function splitLayout3_cstm() {
    document.getElementById("splitLayout3Cstm").style.display = null;
    document.getElementById("splitLayout3Wthn").style.display = "none";
    document.getElementById("submitBtn").style.display = null;
}

function splitLayout3_wthn() {
    document.getElementById("splitLayout3Cstm").style.display = "none";
    document.getElementById("splitLayout3Wthn").style.display = null;
    document.getElementById("submitBtn").style.display = null;
}

function wmLayout_image() {
    document.getElementById("wmLayoutImage").style.display = null;
    document.getElementById("wmLayoutText").style.display = "none";
    document.getElementById("submitBtn").style.display = null;
}

function wmLayout_text() {
    document.getElementById("wmLayoutImage").style.display = "none";
    document.getElementById("wmLayoutText").style.display = null;
    document.getElementById("submitBtn").style.display = null;
}
