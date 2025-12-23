# 📝 Modern To-Do List (PHP)

A simple yet modern **To-Do List web application** built with **native PHP**, JSON-based storage, and a clean UI. This project is designed to be lightweight, easy to understand, and perfect for learning basic CRUD operations without a database.

🔗 **Live Demo (GitHub Pages)**  
> Generated via GitHub Actions (PHP → static HTML)


https://github.com/user-attachments/assets/005c377d-d7ea-47a5-a18f-c054f1da5e35


## ✨ Features

- ➕ Create new tasks
- ✏️ Edit existing tasks
- ✅ Mark tasks as completed
- 🗑️ Delete tasks
- 🔍 Filter tasks (All / Completed / Unfinished)
- 📊 Task statistics dashboard
- 💾 Persistent storage using `data.json`
- 🎨 Modern, responsive UI
- ⚡ Smooth animations & interactions

---

## 🛠️ Tech Stack

- **PHP (Native)** — core logic & rendering
- **JSON** — local data storage (no database)
- **HTML5 & CSS3** — layout & styling
- **JavaScript (Vanilla)** — UI animations
- **GitHub Actions** — build automation
- **GitHub Pages** — static deployment

---

## 📂 Project Structure

```
├── index.php          # Main application logic
├── data.json          # Task storage
├── css/
│   └── style.css      # Styles
├── .github/
│   └── workflows/
│       └── build.yml  # PHP → HTML build workflow
└── README.md
```

---

## 🚀 How It Works

- Tasks are stored in a local **JSON file** (`data.json`)
- All CRUD actions are handled via **POST & GET** requests
- Filtering is controlled via URL query (`?filter=done`, `?filter=undone`)
- On GitHub Pages:
  - `index.php` is executed during **GitHub Actions build**
  - Output is converted into static `index.html`

> ⚠️ GitHub Pages does **not** run PHP directly — this project uses a build workflow.

---

## 🧪 Run Locally

```bash
php -S localhost:8000
```

Then open:
```
http://localhost:8000
```

---

## 🎯 Learning Goals

This project is great for:

- PHP beginners
- Understanding CRUD without a database
- Learning how PHP logic maps to UI
- GitHub Actions basics
- Static deployment workflows

---

## 📸 Preview

> Simple, clean, and focused on productivity ✨

---

## 📄 License

This project is open-source and available under the **MIT License**.

---

Made with ☕ and PHP

