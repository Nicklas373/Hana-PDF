@extends('layouts.alternate-layout')
@section('content')
<div class="px-4 md:px-12" id="html">
    <section class="flex flex-wrap items-center justify-start sub-headline-viewport max-w-lg lg:max-w-6xl">
        <div class="text-start mx-6">
            <div class="font-magistral font-bold text-pc4 text-3xl lg:text-7xl mb-4 lg:mb-8">HTML to PDF</div>
            <div class="font-quicksand font-light text-md lg:text-3xl text-lt1">Convert web pages into PDF documents.</div>
        </div>
    </section>
  <form method="POST" enctype="multipart/form-data" class="flex flex-col p-2" id="formHTML">
    {{ csrf_field() }}
    <div class="w-full p-2 lg:w-3/6 bg-transparent rounded-lg bg-opacity-50 p-2 mt-2 lg:mb-8 lg:mx-2">
        <p class="block mb-2 font-quicksand text-xl font-bold text-pc4">Write the Website URL</p>
        <div class="flex">
            <span class="inline-flex items-center px-3 text-sm text-ac bg-ac border border-ac rounded-s-lg">
                <img class="h-6 w-6 text-lt1" src="{{ asset('assets/icons/website.svg') }}" />
            </span>
            <input type="text" id="urlToPDF" name="urlToPDF" class="flowbite-drop-zone font-poppins rounded-r-lg block w-full cursor-pointer border border-gray-300 text-sm text-slate-900 shadow-inner focus:ring-sky-400" onfocusin="checkValidation('urlToPDF')" onfocusout="checkValidation('urlToPDF')" placeholder="https://pdf.hana-ci.com" />
        </div>
    </div>
    <div class="flex flex-col mt-4 w-full lg:mx-1.5 lg:w-5/6">
        <div class="flex flex-col mx-2 mt-4 lg:mx-1.5 lg:mb-6">
            <label id="margin" class="block mb-2 font-quicksand text-xl font-bold text-pc4" for="pageMargin">Margin</label>
            <div class="flex flex-row w-full">
                <input id="pageMargin" name="pageMarginText" type="range" min="0" max="100" value="20" step="1" class="w-full lg:w-3/6 h-2 mt-4 accent-ac rounded-lg cursor-pointer mx-2 lg:mx-1.5" oninput="showVal(this.value,'html')" onchange="showVal(this.value,'html')">
                <label id="pageMarginValueText" class="font-semibold text-md text-lt1 ml-3 mt-2.5 font-quicksand" for="pageMargin">20 px</label>
            </div>
        </div>
        <div class="flex flex-col mx-2 mb-6 lg:mx-1.5 lg:mb-8">
            <label for="firstRadio" class="block mb-2 font-quicksand text-xl font-bold text-pc4">Orientation</label>
            <ul class="grid grid-cols-1 lg:grid-cols-2 xl:flex-row mt-2 lg:mt-0 mb-4 lg:w-3/6 lg:gap-2">
                <li id="firstCol" class="bg-transparent border-2 border-lt backdrop-filter backdrop-blur-md rounded-lg bg-opacity-50 p-2 mt-2 mx-2 lg:mx-0">
                    <input type="text" id="firstInput" class="" style="display: none;" value="orientation">
                    <div class="flex" id="firstChk">
                        <div class="flex items-center h-5">
                            <input id="firstRadio" name="pageOrientation" value="landscape" aria-describedby="helper-firstRadioText" type="radio" class="w-4 h-4 mt-1.5 text-ac border-ac ring-ac ring-0 hover:ring-2 hover:ring-ac focus:ring-0">
                        </div>
                        <div class="ml-4">
                            <label for="firstRadio" class="font-semibold text-md text-lt1 font-quicksand" id="firstRadioText">Landscape</label>
                        </div>
                    </div>
                </li>
                <li id="secondCol" class="bg-transparent border-2 border-lt backdrop-filter backdrop-blur-md rounded-lg bg-opacity-50 p-2 mt-2 mx-2">
                    <input type="text" id="secondInput" class="" style="display: none;" value="html">
                    <div class="flex" id="secondChk">
                        <div class="flex items-center h-5">
                            <input id="secondRadio" name="pageOrientation" value="portrait" aria-describedby="helper-secondRadioText" type="radio" class="w-4 h-4 mt-1.5 text-ac border-ac ring-ac ring-0 hover:ring-2 hover:ring-pc2 focus:ring-0">
                        </div>
                        <div class="ml-4">
                            <label for="secondRadio" class="font-semibold text-md text-lt1 font-quicksand" id="secondRadioText">Portrait</label>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
        <div class="flex flex-col mt-2 w-full lg:mx-1.5 lg:w-full">
            <div dir="ltl">
                <button type="button" id="submitBtn" class="mx-auto mt-6 mb-8 sm:mb-6 font-quicksand font-semibold bg-transparent border-2 border-lt backdrop-filter backdrop-blur-md rounded-lg bg-opacity-50 text-lt1 rounded-lg cursor-pointer w-full lg:w-3/6 h-10" data-ripple-light="true">Convert</button>
            </div>
            <div class="flex flex-col">
                @include('includes.alert')
            </div>
        </div>
    </div>
  </form>
  @stop
</div>
