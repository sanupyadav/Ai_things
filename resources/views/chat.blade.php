<x-app-layout>
  <x-slot name="header">
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
          {{ __('Chat') }}
      </h2>
  </x-slot>
  <head>
      <meta charset="UTF-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1" />
      <meta name="csrf-token" content="{{ csrf_token() }}" />
      <title>Chat</title>
      <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
  </head>
  <body class="bg-gray-900 dark:bg-gray-900 min-h-screen flex items-center justify-center p-4">
      <div class="flex flex-col gap-4 max-w-4xl w-full bg-gray-800 rounded-xl shadow-lg p-6" style="margin: auto; margin-top: 80px;">
          <!-- Chat messages -->
          <div id="chatWindow" class="overflow-y-auto p-4 bg-gray-900 rounded-lg flex flex-col gap-3 border border-gray-700" style="height: 400px;">
              @foreach($chatHistory as $msg)
                  @if(in_array($msg['sender'], ['user', 'assistant']))
                      <div class="flex justify-{{ $msg['sender'] === 'user' ? 'end' : 'start' }} items-start gap-2">
                          <div class="px-4 py-2 rounded-lg max-w-[80%] break-words border border-gray-600 transition-all duration-200 hover:shadow-md {{ $msg['sender'] === 'user' ? 'bg-blue-700 text-white' : 'bg-gray-700 text-gray-200' }}">
                              <pre class="whitespace-pre-wrap font-mono text-sm">{!! nl2br(e($msg['text'])) !!}</pre>
                          </div>
                      </div>
                  @endif
              @endforeach
          </div>

          <!-- Typing indicator -->
          <div id="typingIndicator" class="hidden flex justify-start items-center">
              <div class="px-4 py-2 rounded-lg max-w-xs break-words bg-gray-700 text-gray-300 border italic">
                  Agent is typing...
              </div>
          </div>

          <!-- Input + Clear Button -->
          <form id="chatForm" class="flex gap-2 items-center">
              @csrf
              <input
                  type="text"
                  name="message"
                  id="messageInput"
                  class="flex-1 border rounded-full px-4 py-2 focus:ring-2 focus:ring-blue-500 bg-gray-700 text-gray-100 border-gray-600 placeholder-gray-400 focus:outline-none transition-all duration-200"
                  placeholder="Ask me anything..."
                  required
                  maxlength="255"
              />
              <button
                  type="submit"
                  id="sendBtn"
                  class="bg-blue-700 text-white px-4 py-2 rounded-full hover:bg-blue-800 focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all duration-200 flex items-center gap-2"
              >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                  </svg>
              </button>
              <button
                  type="button"
                  id="clearHistoryBtn"
                  class="bg-red-600 text-white px-4 py-2 rounded-full hover:bg-red-700 focus:ring-2 focus:ring-red-500 transition-all duration-200"
              >
                  Clear
              </button>
              @error('message')
                  <span class="text-red-500 text-sm">{{ $message }}</span>
              @enderror
          </form>
      </div>

      <!-- JavaScript -->
      <script>
          const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content");
          const chatWindow = document.getElementById("chatWindow");
          const typingIndicator = document.getElementById("typingIndicator");
          const sendBtn = document.getElementById("sendBtn");
          const messageInput = document.getElementById("messageInput");
          const chatForm = document.getElementById("chatForm");

          // Typewriter effect
          function typeMessage(container, text, speed = 5) {
              let i = 0;
              const interval = setInterval(() => {
                  container.textContent += text.charAt(i);
                  i++;
                  if (i >= text.length) clearInterval(interval);
              }, speed);
          }

          // Send message
          chatForm.addEventListener("submit", async (event) => {
              event.preventDefault();
              const message = messageInput.value.trim();
              if (!message) return;

              // Disable input and button
              messageInput.disabled = true;
              sendBtn.disabled = true;

              // Append user message
              const userDiv = document.createElement("div");
              userDiv.className = "flex justify-end items-start gap-2";
              userDiv.innerHTML = `<div class="px-4 py-2 rounded-lg max-w-[80%] break-words bg-blue-700 text-white border border-gray-600"><pre class="whitespace-pre-wrap font-mono text-sm">${message}</pre></div>`;
              chatWindow.appendChild(userDiv);
              chatWindow.scrollTo({ top: chatWindow.scrollHeight, behavior: "smooth" });

              messageInput.value = "";

              // Show typing indicator
              typingIndicator.classList.remove("hidden");
              chatWindow.appendChild(typingIndicator);
              chatWindow.scrollTo({ top: chatWindow.scrollHeight, behavior: "smooth" });

              try {
                  const response = await fetch("{{ route('chat.send') }}", {
                      method: "POST",
                      headers: {
                          "Content-Type": "application/json",
                          "X-CSRF-TOKEN": csrfToken,
                      },
                      body: JSON.stringify({ message }),
                  });

                  if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                  const data = await response.json();

                  if (data.success) {
                      typingIndicator.classList.add("hidden");

                      const botDiv = document.createElement("div");
                      botDiv.className = "flex justify-start items-start gap-2";
                      const botMessage = document.createElement("div");
                      botMessage.className = "px-4 py-2 rounded-lg max-w-[80%] break-words bg-gray-700 text-gray-200 border border-gray-600";
                      const pre = document.createElement("pre");
                      pre.className = "whitespace-pre-wrap font-mono text-sm";
                      botMessage.appendChild(pre);
                      botDiv.appendChild(botMessage);
                      chatWindow.appendChild(botDiv);

                      const lastMsg = data.chatHistory?.[data.chatHistory.length - 1]?.text || "No response received.";
                      typeMessage(pre, lastMsg, 40);

                      chatWindow.scrollTo({ top: chatWindow.scrollHeight, behavior: "smooth" });
                  } else {
                      throw new Error(data.error || "Server returned unsuccessful response.");
                  }
              } catch (error) {
                  typingIndicator.classList.add("hidden");
                  console.error("Error:", error);
                  const errorDiv = document.createElement("div");
                  errorDiv.className = "flex justify-start items-start gap-2";
                  errorDiv.innerHTML = `<div class="px-4 py-2 rounded-lg max-w-[80%] break-words bg-red-600 text-white border border-gray-600"><pre class="whitespace-pre-wrap font-mono text-sm">Error: Could not send message. Please try again.</pre></div>`;
                  chatWindow.appendChild(errorDiv);
                  chatWindow.scrollTo({ top: chatWindow.scrollHeight, behavior: "smooth" });
              } finally {
                  // Re-enable input and button
                  messageInput.disabled = false;
                  sendBtn.disabled = false;
                  messageInput.focus();
              }
          });

          // Clear history
          document.getElementById("clearHistoryBtn").addEventListener("click", async () => {
              if (!confirm("Are you sure you want to clear the chat history?")) return;

              try {
                  const response = await fetch("{{ route('chat.clear') }}", {
                      method: "POST",
                      headers: {
                          "Content-Type": "application/json",
                          "X-CSRF-TOKEN": csrfToken,
                      },
                  });

                  if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                  const data = await response.json();

                  if (data.success) {
                      chatWindow.innerHTML = "";
                  } else {
                      throw new Error(data.error || "Failed to clear chat history.");
                  }
              } catch (error) {
                  console.error("Error clearing history:", error);
                  const errorDiv = document.createElement("div");
                  errorDiv.className = "flex justify-start items-start gap-2";
                  errorDiv.innerHTML = `<div class="px-4 py-2 rounded-lg max-w-[80%] break-words bg-red-600 text-white border border-gray-600"><pre class="whitespace-pre-wrap font-mono text-sm">Error: Could not clear history. Please try again.</pre></div>`;
                  chatWindow.appendChild(errorDiv);
                  chatWindow.scrollTo({ top: chatWindow.scrollHeight, behavior: "smooth" });
              }
          });

          // Auto-scroll on load
          document.addEventListener("DOMContentLoaded", () => {
              chatWindow.scrollTop = chatWindow.scrollHeight;
          });

          // Auto-scroll on new messages
          const observer = new MutationObserver(() => {
              chatWindow.scrollTo({ top: chatWindow.scrollHeight, behavior: "smooth" });
          });
          observer.observe(chatWindow, { childList: true, subtree: true });
        
      </script>
  </body>
</x-app-layout>