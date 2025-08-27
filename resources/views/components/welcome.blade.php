<div class="p-6 lg:p-8 bg-white dark:bg-gray-800 dark:bg-gradient-to-bl dark:from-gray-700/50 dark:via-transparent border-b border-gray-200 dark:border-gray-700">
    <x-application-logo class="block h-12 w-auto" />

    <h1 class="mt-8 text-2xl font-bold text-gray-900 dark:text-white">
        Welcome to Your AI-Powered Chat Application!
    </h1>

    <p class="mt-6 text-gray-500 dark:text-gray-400 leading-relaxed">
        Dive into intelligent conversations with our Chat AI Agent, designed to provide real-time assistance, answer your queries, and boost your productivity with cutting-edge AI technology.
    </p>
</div>

<div class="bg-gray-200 dark:bg-gray-800 bg-opacity-25 grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8 p-6 lg:p-8">
    <div class="relative bg-white dark:bg-gray-900 rounded-3xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden" style="border-radius: 1.5rem;">
        <div class="absolute inset-0 bg-gradient-to-r from-indigo-500/10 to-purple-500/10 opacity-75"></div>
        <div class="relative p-6">
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" class="size-8 stroke-indigo-600 dark:stroke-indigo-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                </svg>
                <h2 class="mr--11 text-xl font-semibold text-gray-900 dark:text-white">
                    <a href="{{ route('chat') }}">Chat AI Agent</a>
                </h2>
            </div>

            <p class="mt-4 text-gray-600 dark:text-gray-300 text-sm leading-relaxed">
                Connect with our advanced Chat AI Agent for seamless, real-time conversations. Whether you need answers, automation, or personalized support, our AI is here to help.
            </p>

            <div class="mt-6">
                <a href="{{ route('chat') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 dark:bg-indigo-500 text-white font-semibold rounded-lg hover:bg-indigo-700 dark:hover:bg-indigo-600 transition-colors duration-200">
                    Start Chatting Now
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="ms-2 size-5 fill-white">
                        <path fill-rule="evenodd" d="M5 10a.75.75 0 01.75-.75h6.638L10.23 7.29a.75.75 0 111.04-1.08l3.5 3.25a.75.75 0 010 1.08l-3.5 3.25a.75.75 0 11-1.04-1.08l2.158-1.96H5.75A.75.75 0 015 10z" clip-rule="evenodd" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
</div>