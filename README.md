# 🎓 Masar — Smart Course Recommendation System
### Backend (Laravel) — Graduation Project | 5th Year

> **Masar** هو نظام توصية ذكي للكورسات يساعد الطلاب على اكتشاف الكورسات المناسبة من Coursera بناءً على خلفيتهم الأكاديمية واهتماماتهم.

---

## 🏗️ System Architecture

المشروع مبني من **3 خدمات منفصلة** تتكامل مع بعض:

```
┌─────────────────────────────────────────────────────┐
│           Next.js Frontend (port 3000)              │
│         React UI + TypeScript + Tailwind            │
└──────────────┬──────────────────┬───────────────────┘
               │ REST API         │ REST API
               ▼                  ▼
┌──────────────────────┐  ┌──────────────────────────┐
│  Laravel Backend     │  │  Python ML Services      │
│     (port 8000)      │  │                          │
│                      │  │  ① Recommender (8002)    │
│  - Sanctum Auth      │  │    T5 + LoRA + SBERT     │
│  - Spatie RBAC       │  │    → يقترح كورسات        │
│  - Queue Jobs        │  │                          │
│  - Eloquent ORM      │  │  ② Quiz Generator (8001) │
│  - SQLite/MySQL      │  │    Gemini 2.5 Flash       │
│                      │  │    → يولد أسئلة تشخيصية  │
└──────────────────────┘  └──────────────────────────┘
```

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 12 |
| Language | PHP 8.2+ |
| Authentication | Laravel Sanctum (API Tokens) |
| Authorization | Spatie Laravel Permission (RBAC) |
| Database | SQLite (dev) / MySQL (prod) |
| Queue | Laravel Queue (database driver) |
| Cache | Laravel Cache (database driver) |
| Session | Laravel Session (database driver) |
| Assets | Vite |
| Testing | PHPUnit 11 |

---

## 🤖 ML Services

### Service ① — Recommender (FastAPI · port 8002)

يستقبل نص المنهج من المستخدم ويرجع قائمة بأفضل الكورسات المقترحة.

**Models المستخدمة:**

| Model | الدور |
|-------|-------|
| `t5-base` + LoRA (fine-tuned) | يولّد عناوين كورسات مرشحة من نص المنهج |
| `all-MiniLM-L6-v2` (SBERT) | يحسب التشابه الدلالي بين المنهج والكاتالوج |

**الـ Endpoint:**
```
POST http://localhost:8002/api/recommend
Body: { "syllabus_text": "..." }
Response: { "recommendations": [ { "rank", "course_title", "score" } ] }
```

**كيف يشتغل؟**
1. يبني prompt ويمرّره لـ T5+LoRA → يولّد `NUM_CANDIDATES=5` عناوين مرشحة
2. لكل عنوان مرشح يحسب 3 scores:
   - `W_TITLE=0.20` — تشابه العنوان المولّد مع كاتالوج الكورسات
   - `W_MATCHER=0.75` — تشابه نص المنهج مع محتوى الكاتالوج (chunk-based)
   - `W_GEN=0.05` — تشابه العنوان المولّد مع محتوى الكاتالوج
3. يدمج الـ scores ويرجع أفضل `TOP_N=5` كورسات

**ملف الـ Catalog:** `model/catalog.csv` — يُحمَّل مرة واحدة عند الـ startup ويُبنى index للـ chunks

---

### Service ② — Quiz Generator (FastAPI · port 8001)

يولّد اختبار تشخيصي من 25 سؤال بناءً على منهج الكورس المقترح.

**Model المستخدم:** `Gemini 2.5 Flash` (Google Generative AI)

**الـ Endpoint:**
```
POST http://localhost:8001/api/generate-quiz
Body: { "category": "...", "placement_topics": [...] }
Response: { "questions": [ { "question_text", "options", "correct_answer", "difficulty_level" } ] }
```

**كيف يشتغل؟**
1. Laravel يستدعي `startCategoryPlacementTest` ويرسل النتيجة مباشرة كـ body
2. الـ service يبني prompt احترافي ويرسله لـ Gemini
3. Gemini يرجع 25 سؤال JSON مقسّمة حسب الصعوبة:
   - أول 7 أسئلة → Beginner
   - أسئلة 8-17 → Intermediate
   - أسئلة 18-25 → Advanced
4. يتحقق من صحة الـ JSON قبل ما يرجعه للـ Frontend

---

## ⚙️ Laravel Services المستخدمة

### 🔐 Laravel Sanctum
API Token authentication — كل request من Next.js يرسل token يتحقق منه Sanctum.

### 🛡️ Spatie Laravel Permission (RBAC)
أدوار المستخدمين: `Admin` · `Student` · `Guest`

### 📬 Laravel Queue
يُرسل طلبات الـ ML بشكل async حتى ما يتأخر الـ response على المستخدم:
```bash
php artisan queue:listen --tries=1 --timeout=0
```

### 💾 Database Migrations
SQLite في التطوير، MySQL في الإنتاج.

---

## 📁 Project Structure

```
graduation-project/          ← Laravel Backend
├── app/
│   ├── Http/Controllers/    # API Controllers
│   ├── Models/              # Eloquent Models
│   └── Services/            # HTTP calls to ML services
├── config/
│   ├── cors.php             # CORS للـ Next.js
│   └── sanctum.php          # Sanctum config
├── database/migrations/     # DB schema
├── routes/api.php           # API routes
└── .env                     # Environment variables
```

---

## 🚀 Getting Started

### Prerequisites
- PHP >= 8.2 + Composer
- Node.js >= 18
- Python >= 3.10 + pip
- SQLite أو MySQL

### 1. تشغيل Laravel Backend

```bash
git clone https://github.com/shahdzaa/graduation-project.git
cd graduation-project
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
composer run dev     # يشغل serve + queue + vite معاً
```

### 2. تشغيل Recommender Service (port 8002)

```bash
cd ml-recommender/
pip install -r requirements.txt
uvicorn main:app --port 8002 --reload
```

### 3. تشغيل Quiz Generator (port 8001)

```bash
cd ml-quiz/
pip install fastapi uvicorn google-generativeai pydantic python-dotenv
echo "GOOGLE_API_KEY=your_key_here" > .env
uvicorn main:app --port 8001 --reload
```

### 4. تشغيل Frontend

```bash
cd frontend/
npm install
npm run dev     # port 3000
```

---

## 🔗 Environment Variables

### Laravel `.env`
```env
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite

QUEUE_CONNECTION=database
CACHE_STORE=database
SESSION_DRIVER=database

# ML Services
ML_RECOMMENDER_URL=http://localhost:8002
ML_QUIZ_URL=http://localhost:8001

# CORS
SANCTUM_STATEFUL_DOMAINS=localhost:3000
```

### Quiz Generator `.env`
```env
GOOGLE_API_KEY=your_google_api_key_here
```

---

## 🚧 Challenges & Solutions

### ⚠️ Challenge 1 — CORS بين 3 Services مختلفة

**Problem:** الـ Next.js (3000) يتواصل مع Laravel (8000) والـ Python services (8001, 8002) — كل service تحتاج CORS مضبوط.

**Solution:**
- في **Laravel**: ضبط `config/cors.php` لقبول `localhost:3000`
- في **FastAPI**: كل service فيها `CORSMiddleware` مضبوط على `localhost:3000` و `localhost:8000`
- الـ Frontend يتواصل مع Laravel فقط، وLaravel هو اللي يتواصل مع Python (server-to-server، بدون CORS)

---

### ⚠️ Challenge 2 — T5 + LoRA بطيء عند أول request

**Problem:** تحميل الـ T5 model + LoRA adapters عند أول request كان يسبب timeout وتجربة سيئة.

**Solution:**
- استخدام `lifespan` في FastAPI عشان يحمّل كل الـ models مرة واحدة عند الـ startup
- بناء الـ chunk index مرة واحدة وتخزينه في `AppState` في الـ memory
- نتيجة: أول request بطيء (startup)، بقية الـ requests سريعة

---

### ⚠️ Challenge 3 — Gemini ترجع JSON غير صالح أحياناً

**Problem:** Gemini أحياناً كانت تلف الـ JSON بـ markdown code fences (` ```json `) أو ترجع عدد أسئلة غلط.

**Solution:**
- إضافة `generation_config={"response_mime_type": "application/json"}` لإجبار Gemini على JSON خالص
- إضافة fallback لتنظيف الـ code fences إذا وُجدت
- دالة `validate_questions()` تتحقق من عدد الأسئلة (25 بالضبط) وصحة الـ options و `correct_answer`
- إذا فشل الـ validation → `HTTPException 502` بدل ما يرجع بيانات غلط للـ Frontend

---

### ⚠️ Challenge 4 — LoRA Weights لا تنطبق على T5 Base صح

**Problem:** عند تحميل الـ LoRA adapter فوق `t5-base` كانت بعض الأوزان ما تتحمّل بشكل صحيح مما يؤثر على جودة الـ recommendations.

**Solution:**
- استخدام `PeftModel.from_pretrained(base, model_dir, is_trainable=False)` بدل تحميل الأوزان يدوياً
- `.eval()` بعد التحميل مباشرة لتعطيل dropout
- `@torch.no_grad()` على كل inference function لتسريع الأداء وتقليل استهلاك الـ memory

---

### ⚠️ Challenge 5 — Chunk Similarity Index يستهلك ذاكرة كبيرة

**Problem:** الـ Catalog فيه آلاف الكورسات — encoding كل chunks في كل request كان مستحيلاً.

**Solution:**
- `build_index()` يُشغَّل مرة واحدة عند الـ startup
- `chunk_embs` و `title_embs` يُحفظان كـ `torch.Tensor` في الـ GPU/CPU memory
- `doc_boundaries` list تحفظ حدود كل document في الـ chunks array حتى ما نحتاج نبحث

---

### ⚠️ Challenge 6 — Laravel Queue Timeout مع طلبات الـ ML

**Problem:** طلب الـ recommendation من T5 يأخذ وقت — كان الـ queue job يموت بـ timeout قبل ما يكمّل.

**Solution:**
- تشغيل `queue:listen` مع `--timeout=0` في بيئة التطوير
- ضبط `QUEUE_FAILED_DRIVER=database` لتسجيل الـ failures
- في الـ production: ضبط timeout مناسب (120s+) بدل 0

---

### ⚠️ Challenge 7 — Sanctum مع Next.js (SPA vs API Tokens)

**Problem:** Sanctum له وضعين — cookie-based SPA وAPI Tokens. الـ Next.js على domain مختلف فما كانت الـ cookies تشتغل.

**Solution:**
- اخترنا **API Token mode** — كل مستخدم يحصل على token عند الـ login
- الـ Frontend يخزّن الـ token ويرسله في كل request كـ `Authorization: Bearer {token}`

---

### ⚠️ Challenge 8 — Spatie Permissions لا تنعكس فوراً

**Problem:** بعد تعديل صلاحيات مستخدم، الـ API كان يرفض الـ request بسبب cached permissions.

**Solution:**
```bash
php artisan permission:cache-reset
```
وإضافة `PermissionRegistrar::forgetCachedPermissions()` في الـ seeders والـ tests.

---

## 📜 Available Commands

```bash
# Laravel
composer run dev                   # تشغيل كل شي معاً
php artisan migrate:fresh --seed   # إعادة تهيئة DB
php artisan queue:failed           # عرض الـ jobs الفاشلة
php artisan permission:cache-reset # مسح cache الصلاحيات

# Python Services
uvicorn main:app --port 8002 --reload   # Recommender
uvicorn main:app --port 8001 --reload   # Quiz Generator
```

---

## 👥 Team

| Role | Name |
|------|------|
| Frontend (Next.js) | [@Sanako2003](https://github.com/Sanako2003) |
| Backend (Laravel) | [@shahdzaa](https://github.com/shahdzaa) |
| ML Services (Python) | Graduation Team |

---

## 📄 License

This project is developed for academic purposes as part of a graduation requirement.
