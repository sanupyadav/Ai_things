<p align="center">
  <a href="https://laravel.com" target="_blank">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
  </a>
</p>

<p align="center">
  <a href="https://github.com/sanupyadav/Ai_things/actions">
    <img src="https://github.com/sanupyadav/Ai_things/workflows/tests/badge.svg" alt="Build Status">
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

## 🤖 Ai_things — Smart AI Agents in Laravel

A modern AI platform built with **Laravel 11**, featuring multiple AI agents for smart automation, chat, and dynamic prompt handling.  
Supports multiple AI providers including **Ollama**, **OpenAI**, and **GitHub AI**.

---

## ✨ Features

- 🔌 Switch between **Ollama**, **OpenAI**, and **GitHub AI**  
- ⚡ Local inference with **LLaMA 3.2**, **Phi3**, **Gemma**, etc.  
- 📄 Dynamic Blade-based instructions & prompts  
- 💬 Markdown-rendered AI responses  
- ✍️ Typewriter effect for replies  
- 🧠 Model selection UI  
- 🔒 Session-based conversations  
- 🌍 Multi-language instructions support  
- 🎙️ Optional: Speech-to-text & text-to-speech integration  

---

## 🛠 Tech Stack

- **Laravel 11**  
- **TailwindCSS** + **Marked.js**  
- [**LarAgent**](https://github.com/maestroerror/laragent) for AI agent management  
- Ollama / OpenAI / GitHub AI APIs  
- Blade templating  

---

## 📦 Installation

```bash
git clone https://github.com/sanupyadav/Ai_things.git
cd Ai_things

composer install
npm install && npm run dev

cp .env.example .env
php artisan key:generate
