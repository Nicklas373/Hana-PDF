// Global controller
var firstArea = document.getElementById('firstChk')
var secondArea = document.getElementById('secondChk')
var thirdArea = document.getElementById('thirdChk')
var fourthArea = document.getElementById('fourthChk')
var firstColumnArea = document.getElementById('firstCol')
var secondColumnArea = document.getElementById('secondCol')
var thirdColumnArea = document.getElementById('thirdCol')
var fourthColumnArea = document.getElementById('fourthCol')
var isImageMosaicArea = document.getElementById('isMosaicImageArea')
var isTextMosaicArea = document.getElementById('isMosaicTextArea')
var firstAreaText = document.getElementById("firstRadioText")
var secondAreaText = document.getElementById("secondRadioText")
var thirdAreaText = document.getElementById("thirdRadioText")
var fourthAreaText = document.getElementById("fourthRadioText")
var firstAreaInput = document.getElementById("firstRadio")
var secondAreaInput = document.getElementById("secondRadio")
var thirdAreaInput = document.getElementById("thirdRadio")
var fourthAreaInput = document.getElementById("fourthRadio")
var firstAreaAltInput = document.getElementById('firstInput')
var secondAreaAltInput = document.getElementById('secondInput')
var thirdAreaAltInput = document.getElementById('thirdInput')
var fourthAreaAltInput = document.getElementById('fourthInput')
var isImageMosaicCheck = document.getElementById('isMosaicImage')
var isTextMosaicCheck = document.getElementById('isMosaicText')
var extImageChkBox = document.getElementById('extImageLayout')
var extImageSwitch = document.getElementById('extImage')
var returnToHomeBtn = document.getElementById('ReturntoHomeBtn')
var timerId = setInterval("reloadIFrame();", 2000)

// Watermark image separate controller
var wmLayoutImageStyleAreaA = document.getElementById('wmChkImageLayoutStyleA')
var wmLayoutImageStyleAreaB = document.getElementById('wmChkImageLayoutStyleB')
var wmImageRotationAreaA = document.getElementById('wmChkImageRotationA')
var wmImageRotationAreaB = document.getElementById('wmChkImageRotationB')
var wmImageRotationAreaC = document.getElementById('wmChkImageRotationC')
var wmImageRotationAreaD = document.getElementById('wmChkImageRotationD')
var wmLayoutImageStyleColumnAreaA = document.getElementById('wmColImageLayoutStyleA')
var wmLayoutImageStyleColumnAreaB = document.getElementById('wmColImageLayoutStyleB')
var wmImageRotationColumnAreaA = document.getElementById('wmColImageRotationA')
var wmImageRotationColumnAreaB = document.getElementById('wmColImageRotationB')
var wmImageRotationColumnAreaC = document.getElementById('wmColImageRotationC')
var wmImageRotationColumnAreaD = document.getElementById('wmColImageRotationD')
var wmLayoutImageStyleAreaTextA = document.getElementById("wmRadioImageLayoutStyleTextA")
var wmLayoutImageStyleAreaTextB = document.getElementById("wmRadioImageLayoutStyleTextB")
var wmImageRotationRadioAreaTextA = document.getElementById("wmRadioImageRotationTextA")
var wmImageRotationRadioAreaTextB = document.getElementById("wmRadioImageRotationTextB")
var wmImageRotationRadioAreaTextC = document.getElementById("wmRadioImageRotationTextC")
var wmImageRotationRadioAreaTextD = document.getElementById("wmRadioImageRotationTextD")
var wmImageRotationRadioAreaInputA = document.getElementById("wmRadioImageRotationA")
var wmImageRotationRadioAreaInputB = document.getElementById("wmRadioImageRotationB")
var wmImageRotationRadioAreaInputC = document.getElementById("wmRadioImageRotationC")
var wmImageRotationRadioAreaInputD = document.getElementById("wmRadioImageRotationD")
var wmLayoutImageRadioAreaInputA = document.getElementById("wmRadioImageLayoutStyleA")
var wmLayoutImageRadioAreaInputB = document.getElementById("wmRadioImageLayoutStyleB")
var wmLayoutImageRadioAltInputA = document.getElementById("wmColImageLayoutStyleInputA")
var wmLayoutImageRadioAltInputB = document.getElementById("wmColImageLayoutStyleInputB")
var wmImageRotationRadioAltInputA = document.getElementById("wmColImageRotationInputA")
var wmImageRotationRadioAltInputB = document.getElementById("wmColImageRotationInputB")
var wmImageRotationRadioAltInputC = document.getElementById("wmColImageRotationInputC")
var wmImageRotationRadioAltInputB = document.getElementById("wmColImageRotationInputD")

// Watermark text separate controller
var wmChkFontFamilyA = document.getElementById('wmChkFontFamilyA')
var wmChkFontFamilyB = document.getElementById('wmChkFontFamilyB')
var wmChkFontFamilyC = document.getElementById('wmChkFontFamilyC')
var wmChkFontFamilyD = document.getElementById('wmChkFontFamilyD')
var wmChkFontFamilyE = document.getElementById('wmChkFontFamilyE')
var wmChkFontFamilyF = document.getElementById('wmChkFontFamilyF')
var wmChkFontStyleA = document.getElementById('wmChkFontStyleA')
var wmChkFontStyleB = document.getElementById('wmChkFontStyleB')
var wmChkFontStyleC = document.getElementById('wmChkFontStyleC')
var wmChkLayoutStyleA = document.getElementById('wmChkLayoutStyleA')
var wmChkLayoutStyleB = document.getElementById('wmChkLayoutStyleB')
var wmChkRotationA = document.getElementById('wmChkRotationA')
var wmChkRotationB = document.getElementById('wmChkRotationB')
var wmChkRotationC = document.getElementById('wmChkRotationC')
var wmChkRotationD = document.getElementById('wmChkRotationD')
var watermarkFontColor = document.getElementById('watermarkFontColor')
var wmFontColorInput = document.getElementById('wmFontColorPicker')
var wmColFontFamilyA = document.getElementById('wmColFontFamilyA')
var wmColFontFamilyB = document.getElementById('wmColFontFamilyB')
var wmColFontFamilyC = document.getElementById('wmColFontFamilyC')
var wmColFontFamilyD = document.getElementById('wmColFontFamilyD')
var wmColFontFamilyE = document.getElementById('wmColFontFamilyE')
var wmColFontFamilyF = document.getElementById('wmColFontFamilyF')
var wmFontSize = document.getElementById('watermarkFontSize')
var wmColFontStyleA = document.getElementById('wmColFontStyleA')
var wmColFontStyleB = document.getElementById('wmColFontStyleB')
var wmColFontStyleC = document.getElementById('wmColFontStyleC')
var wmColLayoutStyleA = document.getElementById('wmColLayoutStyleA')
var wmColLayoutStyleB = document.getElementById('wmColLayoutStyleB')
var wmColRotationA = document.getElementById('wmColRotationA')
var wmColRotationB = document.getElementById('wmColRotationB')
var wmColRotationC = document.getElementById('wmColRotationC')
var wmColRotationD = document.getElementById('wmColRotationD')
var wmColFontFamilyInputA = document.getElementById('wmColFontFamilyInputA')
var wmColFontFamilyInputB = document.getElementById('wmColFontFamilyInputB')
var wmColFontFamilyInputC = document.getElementById('wmColFontFamilyInputC')
var wmColFontFamilyInputD = document.getElementById('wmColFontFamilyInputD')
var wmColFontFamilyInputE = document.getElementById('wmColFontFamilyInputE')
var wmColFontFamilyInputF = document.getElementById('wmColFontFamilyInputF')
var wmColFontStyleInputA = document.getElementById('wmColFontStyleInputA')
var wmColFontStyleInputB = document.getElementById('wmColFontStyleInputB')
var wmColFontStyleInputC = document.getElementById('wmColFontStyleInputC')
var wmColLayoutStyleInputA = document.getElementById('wmColLayoutStyleInputA')
var wmColLayoutStyleInputB = document.getElementById('wmColLayoutStyleInputB')
var wmColRotationInputA = document.getElementById('wmColRotationInputA')
var wmColRotationInputB = document.getElementById('wmColRotationInputB')
var wmColRotationInputC = document.getElementById('wmColRotationInputC')
var wmColRotationInputD = document.getElementById('wmColRotationInputD')
var wmRadioFontFamilyA = document.getElementById('wmRadioFontFamilyA')
var wmRadioFontFamilyB = document.getElementById('wmRadioFontFamilyB')
var wmRadioFontFamilyC = document.getElementById('wmRadioFontFamilyC')
var wmRadioFontFamilyD = document.getElementById('wmRadioFontFamilyD')
var wmRadioFontFamilyE = document.getElementById('wmRadioFontFamilyE')
var wmRadioFontFamilyF = document.getElementById('wmRadioFontFamilyF')
var wmRadioFontStyleA = document.getElementById('wmRadioFontStyleA')
var wmRadioFontStyleB = document.getElementById('wmRadioFontStyleB')
var wmRadioFontStyleC = document.getElementById('wmRadioFontStyleC')
var wmRadioLayoutStyleA = document.getElementById('wmRadioLayoutStyleA')
var wmRadioLayoutStyleB = document.getElementById('wmRadioLayoutStyleB')
var wmRadioRotationA = document.getElementById('wmRadioRotationA')
var wmRadioRotationB = document.getElementById('wmRadioRotationB')
var wmRadioRotationC = document.getElementById('wmRadioRotationC')
var wmRadioRotationD = document.getElementById('wmRadioRotationD')
var wmRadioFontFamilyTextA = document.getElementById('wmRadioFontFamilyTextA')
var wmRadioFontFamilyTextB = document.getElementById('wmRadioFontFamilyTextB')
var wmRadioFontFamilyTextC = document.getElementById('wmRadioFontFamilyTextC')
var wmRadioFontFamilyTextD = document.getElementById('wmRadioFontFamilyTextD')
var wmRadioFontFamilyTextE = document.getElementById('wmRadioFontFamilyTextE')
var wmRadioFontFamilyTextF = document.getElementById('wmRadioFontFamilyTextF')
var wmRadioFontStyleTextA = document.getElementById('wmRadioFontStyleTextA')
var wmRadioFontStyleTextB = document.getElementById('wmRadioFontStyleTextB')
var wmRadioFontStyleTextC = document.getElementById('wmRadioFontStyleTextC')
var wmRadioLayoutStyleTextA = document.getElementById('wmRadioLayoutStyleTextA')
var wmRadioLayoutStyleTextB = document.getElementById('wmRadioLayoutStyleTextB')
var wmRadioRotationTextA = document.getElementById('wmRadioRotationTextA')
var wmRadioRotationTextB = document.getElementById('wmRadioRotationTextB')
var wmRadioRotationTextC = document.getElementById('wmRadioRotationTextC')
var wmRadioRotationTextD = document.getElementById('wmRadioRotationTextD')
