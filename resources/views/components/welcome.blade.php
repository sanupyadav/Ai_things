<div class="p-6 lg:p-8 bg-white dark:bg-gray-800 dark:bg-gradient-to-bl dark:from-gray-700/50 dark:via-transparent border-b border-gray-200 dark:border-gray-700">
   <x-application-logo class="block h-12 w-auto" />


   <h1 class="mt-6 text-2xl font-bold text-gray-900 dark:text-white">
       Welcome to Your AI Dashboard!
   </h1>


   <p class="mt-4 text-gray-500 dark:text-gray-400 leading-relaxed">
       Explore our AI-powered tools. Connect with our Chat AI Agent for intelligent conversations or use the Speech-to-Text feature to convert audio files into text in real-time.
   </p>
</div>


<div class="bg-gray-200 dark:bg-gray-800 bg-opacity-25 grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8 p-6 lg:p-8">


   <!-- Chat AI Agent Card -->
   <div class="relative bg-white dark:bg-gray-900 rounded-3xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden p-6">
       <div class="absolute inset-0 bg-gradient-to-r from-indigo-500/10 to-purple-500/10 opacity-75 rounded-3xl"></div>
       <div class="relative">
           <div class="flex items-center gap-3">
               <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" class="h-8 w-8 stroke-indigo-600 dark:stroke-indigo-400">
                   <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
               </svg>
               <h2 class="text-xl font-semibold text-gray-900 dark:text-white" style = "margin-left: -40px">
                   <a href="{{ route('chat') }}">Chat AI Agent</a>
               </h2>
           </div>
           <p class="mt-3 text-gray-600 dark:text-gray-300 text-sm leading-relaxed">
               Chat with our AI Agent for real-time assistance, answers, or personalized automation. Boost your productivity with AI-powered conversations.
           </p>
           <div class="mt-4">
               <a href="{{ route('chat') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 dark:bg-indigo-500 text-white font-semibold rounded-lg hover:bg-indigo-700 dark:hover:bg-indigo-600 transition-colors duration-200">
                   Start Chatting
                   <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="ms-2 h-5 w-5 fill-white">
                       <path fill-rule="evenodd" d="M5 10a.75.75 0 01.75-.75h6.638L10.23 7.29a.75.75 0 111.04-1.08l3.5 3.25a.75.75 0 010 1.08l-3.5 3.25a.75.75 0 11-1.04-1.08l2.158-1.96H5.75A.75.75 0 015 10z" clip-rule="evenodd" />
                   </svg>
               </a>
           </div>
       </div>
   </div>


   <!-- Speech-to-Text Card -->
   <div class="relative bg-white dark:bg-gray-900 rounded-3xl shadow-lg hover:shadow-3xl transition-shadow duration-300 overflow-hidden p-6">
       <div class="absolute inset-0 bg-gradient-to-r from-green-400/10 to-teal-400/10 opacity-75 rounded-3xl"></div>
       <div class="relative">
           <div class="flex items-center gap-3">
               <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" class="h-8 w-8 stroke-green-600 dark:stroke-green-400">
                   <path stroke-linecap="round" stroke-linejoin="round" d="M12 1.5v21m8.485-8.485l-6.364 6.364M3.515 12l6.364 6.364M12 1.5l-6.364 6.364" />
               </svg>
               <h2 class="text-xl font-semibold text-gray-900 dark:text-white" style = "margin-left: -40px">
                   <a href="{{ route('audio.processor') }}">Speech-to-Text</a>
               </h2>
           </div>
           <p class="mt-3 text-gray-600 dark:text-gray-300 text-sm leading-relaxed">
               Upload audio files and get accurate text transcriptions instantly. Supports MP3, WAV, OGG, FLAC, M4A, and AAC formats.
           </p>
           <div class="mt-4">
               <a href="{{ route('audio.processor') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 dark:bg-indigo-500 text-white font-semibold rounded-lg hover:bg-indigo-700 dark:hover:bg-indigo-600 transition-colors duration-200">
                   Convert Audio
                   <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="ms-2 h-5 w-5 fill-white">
                       <path fill-rule="evenodd" d="M5 10a.75.75 0 01.75-.75h6.638L10.23 7.29a.75.75 0 111.04-1.08l3.5 3.25a.75.75 0 010 1.08l-3.5 3.25a.75.75 0 11-1.04-1.08l2.158-1.96H5.75A.75.75 0 015 10z" clip-rule="evenodd" />
                   </svg>
               </a>
           </div>
       </div>
   </div>

   <!-- AWS Transcribe Card -->
<div class="relative bg-white dark:bg-gray-900 rounded-3xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden p-6">
    <div class="absolute inset-0 bg-gradient-to-r from-yellow-400/10 to-orange-400/10 opacity-75 rounded-3xl"></div>
    <div class="relative">
        <div class="flex items-center gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" class="h-8 w-8 stroke-yellow-600 dark:stroke-yellow-400">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 1.5v21m8.485-8.485l-6.364 6.364M3.515 12l6.364 6.364M12 1.5l-6.364 6.364" />
            </svg>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white" style="margin-left: -40px">
                <a href="{{ route('audio.aws') }}">AWS Transcribe</a>
            </h2>
        </div>
        <p class="mt-3 text-gray-600 dark:text-gray-300 text-sm leading-relaxed">
            Use AWS Transcribe to convert speech to text with advanced AI. Supports multiple languages, speaker identification, and custom vocabulary.
        </p>
        <div class="mt-4">
            <a href="{{ route('audio.aws') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 dark:bg-indigo-500 text-white font-semibold rounded-lg hover:bg-indigo-700 dark:hover:bg-indigo-600 transition-colors duration-200">
                Start Transcription
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="ms-2 h-5 w-5 fill-white">
                    <path fill-rule="evenodd" d="M5 10a.75.75 0 01.75-.75h6.638L10.23 7.29a.75.75 0 111.04-1.08l3.5 3.25a.75.75 0 010 1.08l-3.5 3.25a.75.75 0 11-1.04-1.08l2.158-1.96H5.75A.75.75 0 015 10z" clip-rule="evenodd" />
                </svg>
            </a>
        </div>
    </div>
</div>



</div>





