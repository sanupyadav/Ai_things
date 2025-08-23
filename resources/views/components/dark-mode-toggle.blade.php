@props(['size' => 'w-10 h-10'])

<button
    x-data="{
        dark: localStorage.getItem('theme') === 'dark' 
              || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches),
        toggle() {
            this.dark = !this.dark
            document.documentElement.classList.toggle('dark', this.dark)
            localStorage.setItem('theme', this.dark ? 'dark' : 'light')
        }
    }"
    x-init="document.documentElement.classList.toggle('dark', dark)"
    @click="toggle"
    type="button"
    class="inline-flex items-center justify-center rounded-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 {{ $size }}"
    aria-label="Toggle dark mode"
>
    <!-- Sun -->
    <svg x-show="!dark" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.364 6.364-1.414-1.414M7.05 7.05 5.636 5.636m12.728 0-1.414 1.414M7.05 16.95l-1.414 1.414" />
        <circle cx="12" cy="12" r="4" stroke-width="1.5" />
    </svg>
    <!-- Moon -->
    <svg x-show="dark" x-cloak class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
        <path d="M21 12.79A9 9 0 1 1 11.21 3a7 7 0 0 0 9.79 9.79Z"/>
    </svg>
</button>
