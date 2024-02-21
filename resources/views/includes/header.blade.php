<nav class="fixed left-0 top-0 z-10 lg:h-16 w-full bg-lt dark:bg-dt bg-opacity-75 backdrop-blur-md backdrop-filter">
    <div class="flex max-w-full flex-wrap items-center justify-between p-2 lg:p-4">
      <a href="/" class="px-2 md:mb-2 md:mt-0.5" type="button" data-ripple-light="true">
        <img class="w-32" src="{{ asset('assets/logo/hana-pdf.svg') }}">
      </a>
      <div class="lg:order-0 mt-2 flex md:order-2 md:mt-0">
        <button data-collapse-toggle="navbar-cta" type="button" class="inline-flex mb-2 lg:-mt-1 lg:mb-0 h-10 w-10 items-center justify-center rounded-lg p-2 text-sm text-dt md:hidden" aria-controls="navbar-cta" aria-expanded="false">
          <span class="sr-only">Open main menu</span>
          <svg class="h-5 w-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 17 14">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h15M1 7h15M1 13h15" />
          </svg>
        </button>
      </div>
      <div class="mt-2 hidden w-full h-auto items-center justify-end md:order-1 md:mb-2 md:mt-0 md:ms-8 md:flex md:w-auto md:flex-1 md:mx-0" id="navbar-cta">
        <ul class="mt-4 flex flex-col rounded-lg font-medium md:mt-0 md:flex-row md:space-x-8 md:border-0 md:p-0 md:px-4">
          <li>
            <a href="/compress" class="font-quicksand block rounded py-2 pl-3 pr-4 font-bold text-lg text-dt1 hover:text-pc md:p-0" aria-current="page" type="button" data-ripple-light="true">Compress</a>
          </li>
          <li class="relative">
            <button id="dropdownNavbarLink" data-dropdown-toggle="dropdownNavbar" data-dropdown-placement="bottom" value="0" class="font-quicksand flex w-full items-center justify-between rounded py-2 pl-3 pr-4 font-bold text-lg text-dt1 hover:text-pc md:p-0" onClick="dropdownManage()" type="button" data-ripple-light="true">
              Convert
              <svg id="dropdownNavbarImage" class="ml-2.5 h-2.5 -mt-0.5 w-2.5 rotate-[-90deg] transform-gpu duration-500 ease-in-out" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4" />
              </svg>
            </button>
            <div id="dropdownNavbar" value="0" class="animate-fade z-10 hidden h-auto w-full lg:w-32 absolute top-full  transform-gpu transform translate-x-0 rounded-lg bg-lt2 bg-opacity-75 backdrop-blur-md backdrop-filter duration-500">
              <ul class="py-2 text-sm">
                <li>
                  <a href="/cnvToPDF">
                    <button id="cnvToPDFdropdown" type="button" class="font-quicksand flex w-full items-center justify-between rounded py-2 pl-3 pr-4 font-bold text-lg text-dt1 hover:text-pc" onClick="dropdownCnvToPDF()" type="button" data-ripple-light="true">To PDF</button>
                  </a>
                </li>
                <li>
                  <a href="/cnvFromPDF">
                    <button id="cnvFromPDFdropdown" type="button" class="font-quicksand flex w-full items-center justify-between rounded py-2 pl-3 pr-4 font-bold text-lg text-dt1 hover:text-pc" onClick="dropdownCnvFromPDF()" type="button" data-ripple-light="true">From PDF</button>
                  </a>
                </li>
                <li>
                  <a href="/htmltopdf">
                    <button id="cnvFromPDFdropdown" type="button" class="font-quicksand flex w-full items-center justify-between rounded py-2 pl-3 pr-4 font-bold text-lg text-dt1 hover:text-pc" onClick="dropdownCnvFromPDF()" type="button" data-ripple-light="true">From HTML</button>
                  </a>
                </li>
              </ul>
            </div>
          </li>
          <li>
            <a href="/merge" class="font-quicksand block rounded py-2 pl-3 pr-4 font-bold text-lg text-dt1 hover:text-pc md:p-0" aria-current="page" type="button" data-ripple-light="true">Merge</a>
          </li>
          <li>
            <a href="/split" class="font-quicksand block rounded py-2 pl-3 pr-4 font-bold text-lg text-dt1 hover:text-pc md:p-0" aria-current="page" type="button" data-ripple-light="true">Split</a>
          </li>
          <li>
            <a href="/watermark" class="font-quicksand block rounded py-2 pl-3 pr-4 font-bold text-lg text-dt1 hover:text-pc md:p-0" aria-current="page" type="button" data-ripple-light="true">Watermark</a>
          </li>
        </ul>
      </div>
    </div>
</nav>
