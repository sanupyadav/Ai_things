<x-app-layout>
 <x-slot name="header">
     <h2 class="font-semibold text-x dark:text-gray-200 leading-tight">
         {{ __('Vision Agent') }}
     </h2>
 </x-slot>

 <head>
     <meta charset="UTF-8" />
     <meta name="viewport" content="width=device-width, initial-scale=1" />
     <meta name="csrf-token" content="{{ csrf_token() }}" />
     <title>Vision Agent</title>
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
                     <p class="text-base font-medium">Upload an image to get started</p>
                     <p class="text-xs mt-1">Supported formats: JPG, PNG</p>
                 </div>
             </div>
         </div>

         <!-- Processing indicator -->
         <div id="processingIndicator" class="hidden flex justify-center items-center py-3">
             <div class="flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm">
                 <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                 <span>Analyzing image...</span>
             </div>
         </div>

         <!-- Image Upload Area -->
         <div id="fileUploadArea" class="p-2 bg-gray-700 rounded-md border border-gray-600 hover:border-gray-500 transition-colors duration-200 w-full max-w-md mx-auto">
            <input type="file" id="fileInput" class="hidden" accept="image/*" multiple>
            <div id="uploadDefault" class="text-center cursor-pointer">
                <svg class="mx-auto h-6 w-6 text-gray-400 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
                <p class="text-gray-200 text-sm mb-0.5">Drop your image(s) or click</p>
                <p class="text-gray-400 text-xs">Max size: 5MB each</p>
            </div>
            <div id="fileSelected" class="hidden"></div>
        </div>

         <!-- Buttons -->
         <div class="flex gap-2 justify-center mt-3">
             <button type="button" id="uploadBtn" class="bg-gray-600 text-gray-300 px-4 py-2 rounded-lg hover:bg-gray-500">
                 Select Images
             </button>
             <button type="button" id="processBtn" class="hidden bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                 Analyze
             </button>
             <button type="button" id="clearBtn" class="hidden bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                 Clear
             </button>
         </div>
     </div>

     <!-- JS -->
     <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content");
        const apiUrl = "/api/vision"; // ✅ Use API endpoint directly

        const resultWindow = document.getElementById("resultWindow");
        const processingIndicator = document.getElementById("processingIndicator");
        const uploadBtn = document.getElementById("uploadBtn");
        const processBtn = document.getElementById("processBtn");
        const clearBtn = document.getElementById("clearBtn");
        const fileInput = document.getElementById("fileInput");
        const uploadDefault = document.getElementById("uploadDefault");
        const fileSelected = document.getElementById("fileSelected");
        const placeholderText = document.getElementById("placeholderText");

        let selectedFiles = [];

        uploadBtn.addEventListener('click', () => fileInput.click());
        fileInput.addEventListener('change', e => { if(e.target.files.length) handleFileSelection(e.target.files); });

        function handleFileSelection(files){
            selectedFiles = Array.from(files);
            uploadDefault.classList.add('hidden'); 
            fileSelected.classList.remove('hidden'); 
            processBtn.classList.remove('hidden'); 
            clearBtn.classList.remove('hidden');

            fileSelected.innerHTML = selectedFiles.map(f => 
                `<p class="text-gray-200 text-sm">${f.name} (${formatFileSize(f.size)})</p>`
            ).join('');
        }

        function clearFileSelection(){
            selectedFiles = [];
            fileInput.value = '';
            uploadDefault.classList.remove('hidden'); 
            fileSelected.classList.add('hidden'); 
            processBtn.classList.add('hidden'); 
            clearBtn.classList.add('hidden');
        }
        clearBtn.addEventListener('click', clearFileSelection);

        processBtn.addEventListener('click', async () => {
            if (!selectedFiles.length) return;

            processBtn.disabled = clearBtn.disabled = uploadBtn.disabled = true;
            processingIndicator.classList.remove('hidden');
            placeholderText.style.display = 'none';

            try {
                const formData = new FormData();
                selectedFiles.forEach(file => formData.append('images[]', file));

                const response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' }, // ✅ Expect JSON
                    body: formData
                });

                const result = await response.json();
                if(response.ok && result.status === "success"){
                    displayResult(result.data);
                } else {
                    showError(result.message || "Failed to analyze images");
                }

            } catch (error) {
                showError("Something went wrong while processing");
            } finally {
                processBtn.disabled = clearBtn.disabled = uploadBtn.disabled = false;
                processingIndicator.classList.add('hidden');
            }
        });

        function displayResult(data){
            resultWindow.innerHTML = `
                <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-3 flex items-center gap-2">
                    <span class="text-green-800 font-medium">Analysis Complete</span>
                </div>
                <pre class="text-gray-200 text-sm whitespace-pre-wrap">${JSON.stringify(data, null, 2)}</pre>
            `;
        }

        function showError(message){
            resultWindow.innerHTML = `<div class="text-center text-red-500">${message}</div>`;
        }

        function formatFileSize(bytes){ 
            if(bytes===0) return '0 Bytes'; 
            const k=1024; 
            const sizes=['Bytes','KB','MB','GB']; 
            const i=Math.floor(Math.log(bytes)/Math.log(k)); 
            return parseFloat((bytes/Math.pow(k,i)).toFixed(2))+' '+sizes[i]; 
        }
     </script>
 </body>
</x-app-layout>
