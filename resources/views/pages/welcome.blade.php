@extends('layouts.default') @section('content')
<div class="px-4 md:px-12">
  <section>
    <div class="py-8 px-4 mt-20 max-w-screen-xl z-0">
        <h1 class="mb-4 mt-4 sm:mt-14 text-4xl font-poppins font-semibold tracking-tight leading-none text-sky-400 md:text-5xl lg:text-6xl">Make great work happen from anywhere</h1>
        <p class="mb-4 text-lg font-poppins font-thin text-gray-500 lg:text-xl">Easily and quickly merge, split, compress, convert, and add watermarks to PDF documents</p>
    </div>
  </section>
  <h1 class="font-poppins mb-5 mt-40 ms-8 text-3xl font-semibold leading-none tracking-tight text-slate-700 md:text-2xl lg:text-3xl">Our Features</h1>
  <div class="mx-4 mb-16 gap-8 grid grid-cols-1 p-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5 2xl:mb-20">
    <div class="h-60 md:h-64 px-2 w-full rounded-lg bg-gray-300 backdrop-filter backdrop-blur-md bg-opacity-25 shadow-[inset_30px_30px_60px_-10px_rgba(255,255,255,1)] hover:shadow-[inset_-30px_-30px_60px_-10px_rgba(255,255,255,1)] hover:scale-105 hover:transform-gpu hover:transition hover:delay-150 hover:duration-300 hover:ease-in-out md:mt-0 md:w-11/12" data-ripple-dark="true" type="button">
      <a href="/compress">
        <div dir="rtl">
            <img class="p-2 md:p-4 mr-2 mt-4 xl:mt-6 xl:mr-6 xl:p-2 2xl:mt-8 2xl:mr-8 2xl:p-0" src="/assets/compression.png" alt="" height="64px" width="64px" />
        </div>
        <div class="p-2 mt-4 xl:mt-6 xl:mb-4">
            <h5 class="font-poppins mb-2 text-lg xl:text-2xl xl:mb-4 font-semibold tracking-tight text-slate-900">Compress</h5>
            <p class="font-poppins text-sm text-gray-700">Reduce PDF file size while try to keep optimize for best PDF quality</p>
        </div>
      </a>
    </div>
    <div class="h-60 md:h-64 px-2 w-full rounded-lg bg-gray-300 backdrop-filter backdrop-blur-md bg-opacity-25 shadow-[inset_30px_30px_60px_-10px_rgba(255,255,255,1)] hover:shadow-[inset_-30px_-30px_60px_-10px_rgba(255,255,255,1)] hover:scale-105 hover:transform-gpu hover:transition hover:delay-150 hover:duration-300 hover:ease-in-out md:mt-0 md:w-11/12" type="button" data-ripple-dark="true">
      <a href="/convert">
        <div dir="rtl">
            <img class="p-2 md:p-4 mr-2 mt-4 xl:mt-6 xl:mr-6 xl:p-2 2xl:mt-8 2xl:mr-8 2xl:p-0" src="/assets/convert.png" alt="" height="64px" width="64px" />
        </div>
        <div class="p-2 mt-4 xl:mt-6 xl:mb-4">
            <h5 class="font-poppins mb-2 text-lg xl:text-2xl xl:mb-4 font-semibold tracking-tight text-slate-900">Convert</h5>
            <p class="font-poppins text-sm text-gray-700">Convert PDF or document files into specified document format</p>
        </div>
      </a>
    </div>
    <div class="h-60 md:h-64 px-2 w-full rounded-lg bg-gray-300 backdrop-filter backdrop-blur-md bg-opacity-25 shadow-[inset_30px_30px_60px_-10px_rgba(255,255,255,1)] hover:shadow-[inset_-30px_-30px_60px_-10px_rgba(255,255,255,1)] hover:scale-105 hover:transform-gpu hover:transition hover:delay-150 hover:duration-300 hover:ease-in-out md:mt-0 md:h-62 md:w-11/12" type="button" data-ripple-dark="true">
      <a href="/merge">
        <div dir="rtl">
            <img class="p-2 md:p-4 mr-2 mt-4 xl:mr-6 xl:mt-6 xl:p-2 2xl:mt-8 2xl:mr-8 2xl:p-0" src="/assets/merge.png" alt="" height="64px" width="64px" />
        </div>
        <div class="p-2 mt-4 xl:mt-6 xl:mb-4">
          <h5 class="font-poppins mb-2 text-lg xl:text-2xl xl:mb-4 font-semibold tracking-tight text-slate-900">Merge</h5>
          <p class="font-poppins text-sm text-gray-700">Combine several PDF in the order from user into one merged PDF file</p>
        </div>
      </a>
    </div>
    <div class="h-60 md:h-64 px-2 w-full rounded-lg bg-gray-300 backdrop-filter backdrop-blur-md bg-opacity-25 shadow-[inset_30px_30px_60px_-10px_rgba(255,255,255,1)] hover:shadow-[inset_-30px_-30px_60px_-10px_rgba(255,255,255,1)] hover:scale-105 hover:transform-gpu hover:transition hover:delay-150 hover:duration-300 hover:ease-in-out md:w-11/12" type="button" data-ripple-dark="true">
      <a href="/split">
        <div dir ="rtl">
            <img class="p2 md:p-4 mr-2 mt-4 xl:mr-6 xl:mt-6 xl:p-2 2xl:mt-8 2xl:mr-8 2xl:p-0" src="/assets/split.png" alt="" height="64px" width="64px" />
        </div>
        <div class="p-2 mt-4 xl:mt-6 xl:mb-4">
          <h5 class="font-poppins mb-2 text-lg xl:text-2xl xl:mb-4 font-semibold tracking-tight text-slate-900">Split</h5>
          <p class="font-poppins text-sm text-gray-700">Separate one page or a whole page into independent PDF files</p>
        </div>
      </a>
    </div>
    <div class="h-60 md:h-64 px-2 w-full rounded-lg bg-gray-300 backdrop-filter backdrop-blur-md bg-opacity-25 shadow-[inset_30px_30px_60px_-10px_rgba(255,255,255,1)] hover:shadow-[inset_-30px_-30px_60px_-10px_rgba(255,255,255,1)] hover:scale-105 hover:transform-gpu hover:transition hover:delay-150 hover:duration-300 hover:ease-in-out md:w-11/12" type="button" data-ripple-dark="true">
      <a href="/watermark">
        <div dir ="rtl">
            <img class="p-2 md:p-4 mr-2 mt-4 xl:mr-6 xl:p-2 2xl:mt-8 2xl:mr-8 2xl:p-0" src="/assets/watermark.png" alt="" height="64px" width="64px" />
        </div>
        <div class="p-2 mt-4 xl:mt-6 xl:mb-4">
          <h5 class="font-poppins mb-2 text-lg xl:text-2xl xl:mb-4 font-semibold tracking-tight text-slate-900">Watermark</h5>
          <p class="font-poppins text-sm text-gray-700">Stamp an image or text over PDF to selected pages or all pages</p>
        </div>
      </a>
    </div>
  </div>
  @stop
</div>
