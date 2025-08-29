<x-app-layout>
 <x-slot name="header">
     <h2 class="font-semibold text-x dark:text-gray-200 leading-tight">
         {{ __('AWS Transcribe') }}
     </h2>
 </x-slot>


 <head>
     <meta charset="UTF-8" />
     <meta name="viewport" content="width=device-width, initial-scale=1" />
     <meta name="csrf-token" content="{{ csrf_token() }}" />
     <title>Audio to Text</title>
     <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
 </head>


 <body class="bg-gray-800 min-h-screen flex items-center justify-center p-4">
     <div class="flex flex-col gap-4 max-w-xl w-full bg-gray-700 rounded-xl shadow-lg p-4" style="margin: auto; margin-top: 80px;">
        
         <!-- Result Display Area -->
         <div id="resultWindow" class="overflow-y-auto p-3 bg-gray-900 rounded-lg border border-gray-600 min-h-[250px]">
             <div class="flex items-center justify-center h-full text-gray-400" id="placeholderText">
                 <div class="text-center">
                     <svg class="mx-auto h-10 w-10 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                     </svg>
                     <p class="text-base font-medium">Upload an audio file to get started</p>
                     <p class="text-xs mt-1">Supported formats: MP3, WAV, OGG, FLAC, M4A, AAC</p>
                 </div>
             </div>
         </div>


         <!-- Processing indicator -->
         <div id="processingIndicator" class="hidden flex justify-center items-center py-3">
             <div class="flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm">
                 <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                 <span>Processing audio file...</span>
             </div>
         </div>


         <!-- Audio File Upload Area -->
        <div id="fileUploadArea" class="p-2 bg-gray-700 rounded-md border border-gray-600 hover:border-gray-500 transition-colors duration-200 w-full max-w-md mx-auto">
   <input type="file" id="fileInput" class="hidden" accept=".mp3,.wav,.ogg,.flac,.m4a,.aac">


   <!-- Default upload state -->
   <div id="uploadDefault" class="text-center cursor-pointer">
       <svg class="mx-auto h-6 w-6 text-gray-400 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
       </svg>
       <p class="text-gray-200 text-sm mb-0.5">Drop your audio file or click</p>
       <p class="text-gray-400 text-xs">Max size: 50MB</p>
   </div>


   <!-- File selected state -->
   <div id="fileSelected" class="hidden">
       <div class="flex items-center justify-between p-2 bg-gray-600 rounded-md text-sm">
           <div class="flex items-center gap-2">
               <div class="p-1 bg-blue-600 rounded">
                   <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-1v13M9 19c0 1.105-.895 2-2 2s-2-.895-2-2 .895-2 2-2 2 .895 2 2zm12-1c0 1.105-.895 2-2 2s-2-.895-2-2 .895-2 2-2 2 .895 2 2z"></path>
                   </svg>
               </div>
               <div>
                   <p class="text-gray-200 font-medium text-sm" id="fileName"></p>
                   <p class="text-gray-400 text-xs" id="fileSize"></p>
               </div>
           </div>
           <button id="removeFile" class="text-red-400 hover:text-red-300 p-1 rounded hover:bg-gray-600 transition-colors duration-200">
               <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
               </svg>
           </button>
       </div>
   </div>
</div>


         <!-- Action Buttons -->
         <div class="flex gap-2 justify-center text-sm">
             <button type="button" id="uploadBtn" class="bg-gray-600 text-gray-300 px-4 py-2 rounded-lg hover:bg-gray-500 flex items-center gap-1">
                 Select Audio
             </button>
             <button type="button" id="processBtn" class="hidden bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center gap-1">
                 Convert
             </button>
             <button type="button" id="clearBtn" class="hidden bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                 Clear
             </button>
         </div>
     </div>


     <!-- JS remains the same -->
     <script>
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content");
            const resultWindow = document.getElementById("resultWindow");
            const processingIndicator = document.getElementById("processingIndicator");
            const uploadBtn = document.getElementById("uploadBtn");
            const processBtn = document.getElementById("processBtn");
            const clearBtn = document.getElementById("clearBtn");
            const fileInput = document.getElementById("fileInput");
            const fileUploadArea = document.getElementById("fileUploadArea");
            const uploadDefault = document.getElementById("uploadDefault");
            const fileSelected = document.getElementById("fileSelected");
            const fileName = document.getElementById("fileName");
            const fileSize = document.getElementById("fileSize");
            const removeFile = document.getElementById("removeFile");
            const placeholderText = document.getElementById("placeholderText");

            let selectedFile = null;

            uploadBtn.addEventListener('click', () => fileInput.click());
            fileUploadArea.addEventListener('dragover', e => { e.preventDefault(); fileUploadArea.classList.add('border-blue-500'); });
            fileUploadArea.addEventListener('dragleave', e => { e.preventDefault(); fileUploadArea.classList.remove('border-blue-500'); });
            fileUploadArea.addEventListener('drop', e => { e.preventDefault(); fileUploadArea.classList.remove('border-blue-500'); if(e.dataTransfer.files[0]) handleFileSelection(e.dataTransfer.files[0]); });
            fileUploadArea.addEventListener('click', e => { if(e.target===fileUploadArea||e.target===uploadDefault) fileInput.click(); });
            fileInput.addEventListener('change', e => { if(e.target.files[0]) handleFileSelection(e.target.files[0]); });

            function handleFileSelection(file){
                const allowedTypes=['audio/mpeg','audio/wav','audio/ogg','audio/flac','audio/mp4','audio/aac'];
                if(!allowedTypes.includes(file.type) && !file.name.match(/\.(mp3|wav|ogg|flac|m4a|aac)$/i)){ showError('Invalid file'); return; }
                if(file.size>52428800){ showError('Max 50MB'); return; }

                selectedFile = file;
                fileName.textContent = file.name;
                fileSize.textContent = formatFileSize(file.size);
                uploadDefault.classList.add('hidden'); fileSelected.classList.remove('hidden'); processBtn.classList.remove('hidden'); clearBtn.classList.remove('hidden');
            }

            removeFile.addEventListener('click', clearFileSelection);
            function clearFileSelection(){ selectedFile=null; fileInput.value=''; uploadDefault.classList.remove('hidden'); fileSelected.classList.add('hidden'); processBtn.classList.add('hidden'); clearBtn.classList.add('hidden'); }

            processBtn.addEventListener('click', async ()=>{
                if(!selectedFile) return;
                processBtn.disabled = clearBtn.disabled = uploadBtn.disabled = true;
                processingIndicator.classList.remove('hidden');
                placeholderText.style.display = 'none';
                try{
                    const formData = new FormData();
                    formData.append('audio', selectedFile);
                    const response = await fetch('/api/transcribe', { method:'POST', headers:{'X-CSRF-TOKEN':csrfToken}, body: formData });
                    const data = await response.json();
                    if(data.status === 'success') displayResult(data.text);
                    else showError(data.message || 'Failed to process audio file');
                } catch(e){ showError('Network error. Please try again.'); }
                finally{ processBtn.disabled = clearBtn.disabled = uploadBtn.disabled = false; processingIndicator.classList.add('hidden'); }
            });

            function displayResult(text){
                resultWindow.innerHTML = `
                    <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-2 flex items-center gap-1">
                        <svg class="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span class="text-green-800 font-medium">Transcription Complete</span>
                    </div>
                    <div class="bg-gray-800 border border-gray-600 rounded-lg p-3">
                        <div class="flex justify-between items-center mb-1">
                            <h3 class="text-gray-200 font-medium text-sm">Transcribed Text:</h3>
                            <button onclick="copyToClipboard()" class="text-blue-400 hover:text-blue-300 text-xs flex items-center gap-1">Copy</button>
                        </div>
                        <div class="text-gray-100 leading-relaxed whitespace-pre-wrap text-sm" id="transcribedText">${text}</div>
                    </div>
                `;
            }

            function copyToClipboard(){ 
                const text = document.getElementById('transcribedText').textContent;
                navigator.clipboard.writeText(text).then(()=>{});
            }

            function showError(message){
                resultWindow.innerHTML = `<div class="flex items-center justify-center h-full"><div class="text-center"><div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-2 flex items-center gap-1 justify-center"><span class="text-red-800 font-medium">Error</span></div><p class="text-red-700 text-sm mt-1">${message}</p></div></div>`;
            }

            clearBtn.addEventListener('click', ()=>{
                resultWindow.innerHTML=`<div class="flex items-center justify-center h-full text-gray-400" id="placeholderText"><div class="text-center"><svg class="mx-auto h-10 w-10 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" /></svg><p class="text-base font-medium">Upload an audio file to get started</p><p class="text-xs mt-1">Supported formats: MP3, WAV, OGG, FLAC, M4A, AAC</p></div></div>`;
                clearFileSelection();
            });

            function formatFileSize(bytes){ if(bytes===0) return '0 Bytes'; const k=1024; const sizes=['Bytes','KB','MB','GB']; const i=Math.floor(Math.log(bytes)/Math.log(k)); return parseFloat((bytes/Math.pow(k,i)).toFixed(2))+' '+sizes[i]; }
        </script>
 </body>
</x-app-layout>







