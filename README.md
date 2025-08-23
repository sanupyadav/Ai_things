<p align="center">
  <a href="https://laravel.com" target="_blank">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
  </a>
</p>

<p align="center">
  <a href="https://github.com/laravel/framework/actions">
    <img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status">
  </a>
  <a href="https://packagist.org/packages/laravel/framework">
    <img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads">
  </a>
  <a href="https://packagist.org/packages/laravel/framework">
    <img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version">
  </a>
  <a href="https://packagist.org/packages/laravel/framework">
    <img src="https://img.shields.io/packagist/l/laravel/framework" alt="License">
  </a>
</p>

---

## 🤖 AI Agent — Smart Support Chat in Laravel

A modern AI chatbot powered by Laravel + LLMs (like Ollama, OpenAI, GitHubAI).  
It provides real-time chat, dynamic prompts, markdown rendering, and supports multiple AI providers.

---

## ✨ Features

- 🔌 Switch between **Ollama**, **OpenAI**, and **GitHub AI**
- ⚡ Local inference with **LLaMA 3.2**, **Phi3**, **Gemma**, etc.
- 📄 Dynamic Blade-based instructions & prompts
- 💬 Markdown-rendered AI answers
- ✍️ Typewriter effect for replies
- 🧠 Model selection UI
- 🔒 Session-based conversations
- 🌍 Multi-language instructions (optional)
- 🎙️ (Optional) Speech-to-text & text-to-speech integration

---

## 🛠 Tech Stack

- Laravel 11
- TailwindCSS + Marked.js
- LarAgent (for AI agent management)
- Ollama / OpenAI / GitHub AI APIs
- Blade templating

---

## 📦 Installation

```bash
git clone https://github.com/YOUR_USERNAME/YOUR_REPO.git
cd YOUR_REPO

composer install
npm install && npm run dev

cp .env.example .env
php artisan key:generate
```

---

## ⚙️ Configuration

In `.env` or `config/laragent.php`, define your providers and models:

```php
return [
  'default_provider' => 'ollama',
  'default_model' => 'llama3.2:3b',

  'providers' => [
    'ollama' => [...],
    'openai' => [...],
    'githubai' => [...],
  ],
];
```

---

## 💻 Usage

Start Laravel:

```bash
php artisan serve
```

Open browser: `http://localhost:8000/ai`

Use the chat interface to ask anything related to Pay1 services, or switch providers/models from the config sidebar.

---

## 📚 Laravel Foundation

This project is based on [Laravel](https://laravel.com) — a modern PHP web framework with:

- Expressive routing
- Eloquent ORM
- Artisan CLI
- Middleware & queues
- Blade views

Learn more in the [Laravel Docs](https://laravel.com/docs).

---

## 🙏 Credits

- Laravel by Taylor Otwell & community
- [LarAgent](https://github.com/maestroerror/laragent) by maestroerror
- OpenAI / Ollama / GitHub AI model APIs

---

## 📜 License

This project is open-sourced under the [MIT license](https://opensource.org/licenses/MIT).
