<!DOCTYPE html>
@extends('layouts.alternate-layout')
@section('content')
    <div class="px-4 md:px-12" id="cnvFrPDF">
        <section class="flex flex-wrap items-center justify-start sub-headline-viewport max-w-lg lg:max-w-6xl">
            <div class="text-start mx-6">
                <div class="font-magistral font-bold text-pc4 text-3xl lg:text-7xl mb-4 lg:mb-8">Convert from PDF</div>
                <div class="font-quicksand font-light text-md lg:text-3xl text-lt1">Convert PDF into document or images.</div>
            </div>
        </section>
        <div class="flex flex-col p-2">
            <form action="{{ url('api/v1/file/upload') }}" method="post" class="dropzone flex flex-col lg:flex-row xl:flex-row mx-4 items-center justify-center w-6/6 lg:w-4/6 min-h-96 h-fit lg:h-72 max-h-full lg:overflow-y-auto cursor-pointer bg-lt backdrop-filter backdrop-blur-md rounded-[40px] bg-opacity-15 mb-2" id="dropzoneArea">
                {{ csrf_field() }}
                <div class="flex flex-col items-center justify-content p-4" id="dropzoneUiInit">
                    <img class="p-4 h-24 w-24" src="{{ asset('assets/icons/placeholder_pdf.svg') }}">
                    <p class="mb-2 text-md text-lt3 font-quicksand font-medium">Drop PDF files here</p>
                    <p class="text-xs text-lt3 font-quicksand">Or</p>
                    <button type="button" id="dropzoneUploadInit" class="mx-auto mt-2 p-4 text-xs font-quicksand font-semibold bg-ac text-lt rounded-lg cursor-pointer w-42 h-12 text-center flex items-center justify-center">
                        <svg class="w-6 h-6 text-lt1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd" d="M2 12a10 10 0 1 1 20 0 10 10 0 0 1-20 0Zm11-4.2a1 1 0 1 0-2 0V11H7.8a1 1 0 1 0 0 2H11v3.2a1 1 0 1 0 2 0V13h3.2a1 1 0 1 0 0-2H13V7.8Z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-4">Choose File</span>
                    </button>
                </div>
                <div class="flex flex-col items-center justify-content hidden order-1 border-dashed border-2 border-lt1" id="dropzoneUiExt">
                    <button type="button" id="dropzoneUploadExt" class="mx-auto p-4 bg-transparent text-lt1 rounded-lg cursor-pointer h-48 w-32 text-center flex items-center justify-center">
                        <svg class="w-6 h-6 text-lt1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd" d="M2 12a10 10 0 1 1 20 0 10 10 0 0 1-20 0Zm11-4.2a1 1 0 1 0-2 0V11H7.8a1 1 0 1 0 0 2H11v3.2a1 1 0 1 0 2 0V13h3.2a1 1 0 1 0 0-2H13V7.8Z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            </form>
            <div class="flex flex-col mx-4 mt-8 lg:w-3/6">
                <label for="firstRadio" class="block mb-2 font-quicksand text-xl font-bold text-pc4">Document Format</label>
                <ul class="flex flex-col lg:flex-row xl:flex-row mt-2 lg:mt-0 mb-4">
                    <li id="firstCol" class="w-full p-2 lg:w-2/6 bg-transparent border-2 border-lt backdrop-filter backdrop-blur-md rounded-lg bg-opacity-50 p-2 mt-2 mx-2">
                        <input type="text" id="firstInput" class="" style="display: none;" value="cnvFrPDF">
                        <div class="flex" id="firstChk">
                            <div class="flex items-center h-5">
                                <input id="firstRadio" name="convertType" value="jpg" aria-describedby="helper-firstRadioText" type="radio" class="w-4 h-4 mt-1.5 text-ac border-ac ring-ac ring-0 hover:ring-2 hover:ring-ac focus:ring-0">
                            </div>
                            <div class="ml-4">
                                <label for="firstRadio" class="font-semibold text-md text-lt1 font-quicksand" id="firstRadioText">Image</label>
                                <p id="helper-firstRadioText" class="text-sm mt-1 font-regular font-quicksand text-lt1">(*.jpg)</p>
                            </div>
                        </div>
                    </li>
                    <li id="secondCol" class="w-full p-2 lg:w-2/6 bg-transparent border-2 border-lt backdrop-filter backdrop-blur-md rounded-lg bg-opacity-50 p-2 mt-2 mx-2">
                        <input type="text" id="secondInput" class="" style="display: none;" value="cnvFrPDF">
                        <div class="flex" id="secondChk">
                            <div class="flex items-center h-5">
                                <input id="secondRadio" name="convertType" value="pptx" aria-describedby="helper-secondRadioText" type="radio" class="w-4 h-4 mt-1.5 text-ac border-ac ring-ac ring-0 hover:ring-2 hover:ring-ac focus:ring-0">
                            </div>
                            <div class="ml-4">
                                <label for="secondRadio" class="font-semibold text-md text-lt1 font-quicksand" id="secondRadioText">Powerpoint Presentation</label>
                                <p id="helper-secondRadioText" class="text-sm mt-1 font-regular font-quicksand text-lt1">(*.pptx)</p>
                            </div>
                        </div>
                    </li>
                    <li id="thirdCol" class="w-full p-2 lg:w-2/6 bg-transparent border-2 border-lt backdrop-filter backdrop-blur-md rounded-lg bg-opacity-50 p-2 mt-2 mx-2">
                        <input type="text" id="thirdInput" class="" style="display: none;" value="cnvFrPDF">
                        <div class="flex" id="thirdChk" value="cnvFrPDF">
                            <div class="flex items-center h-5">
                                <input id="thirdRadio" name="convertType" value="excel" aria-describedby="helper-thirdRadioText" type="radio" class="w-4 h-4 mt-1.5 text-ac border-ac ring-ac ring-0 hover:ring-2 hover:ring-ac focus:ring-0">
                            </div>
                            <div class="ml-4">
                                <label for="thirdRadio" class="font-semibold text-md text-lt1 font-quicksand" id="thirdRadioText">Spreadsheet</label>
                                <p id="helper-thirdRadioText" class="text-sm mt-1 font-regular font-quicksand text-lt1">(*.xlsx)</p>
                            </div>
                        </div>
                    </li>
                    <li id="fourthCol" class="w-full p-2 lg:w-2/6 bg-transparent border-2 border-lt backdrop-filter backdrop-blur-md rounded-lg bg-opacity-50 p-2 mt-2 mx-2">
                        <input type="text" id="fourthInput" class="" style="display: none;" value="cnvFrPDF">
                        <div class="flex" id="fourthChk" value="cnvFrPDF">
                            <div class="flex items-center h-5">
                                <input id="fourthRadio" name="convertType" value="docx" aria-describedby="helper-fourthRadioText" type="radio" class="w-4 h-4 mt-1.5 text-ac border-ac ring-ac ring-0 hover:ring-2 hover:ring-ac focus:ring-0">
                            </div>
                            <div class="ml-4">
                                <label for="fourthRadio" class="font-semibold text-md text-lt1 font-quicksand" id="fourthRadioText">Word Document</label>
                                <p id="helper-fourthRadioText" class="text-sm mt-1 font-regular font-quicksand text-lt1">(*.docx)</p>
                            </div>
                        </div>
                    </li>
                </ul>
                <div class="flex flex-col w-full lg:w-2/6 mb-8" id="extImageLayout" style="display: none;">
                    <label for="extImageLayout" class="block mb-2 font-quicksand text-xl font-bold text-pc4 mt-4">Image Options</label>
                    <div class="p-2 w-full lg:w-5/6 flex flex-col bg-transparent border-2 border-lt backdrop-filter backdrop-blur-md rounded-lg bg-opacity-50 p-2 mt-2 mx-2">
                        <label id="extImageLayoutAlt" class="relative inline-flex items-center cursor-pointer mt-2 lg:ms-2">
                            <input type="checkbox" id="extImage" name="extImage" class="sr-only peer">
                            <div class="w-10 h-5 bg-lt3 mb-2 peer-focus:outline-none peer-focus:ring-0 peer-focus:ring-ac rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-lt1  after:content-[''] after:absolute after:bg-lt after:border-ac after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-ac"></div>
                            <span class="ms-3 font-semibold text-md text-lt1 mb-2 font-quicksand">Extract Image only</span>
                        </label>
                    </div>
                </div>
                <div dir="ltl">
                    <button type="submit" id="submitBtn" name="formAction" class="mx-auto mt-6 mb-8 sm:mb-6 font-quicksand font-semibold bg-transparent border-2 border-lt backdrop-filter backdrop-blur-md rounded-lg bg-opacity-50 text-lt1 rounded-lg cursor-pointer w-full lg:w-4/6 h-10" data-ripple-light="true">Convert PDF</button>
                </div>
                <div class="flex flex-col mt-4">
                    @include('includes.alert')
                </div>
            </div>
        </div>
       @stop
    </div>
