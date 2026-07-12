# Masar Recommendation — Database Seeders
مولَّدة تلقائياً من Coursera Dataset

## ترتيب التشغيل (مهم — حسب FK dependencies)

```
01_course_levels.sql
02_course_types.sql
03_syllabus_types.sql
04_domains.sql
05_organizations.sql
06_users_instructors.sql       ← users بدور instructor
06b_instructor_profiles.sql
07_courses.sql
08_course_organizations.sql
09_course_instructors.sql
10_learning_outcomes.sql
11_modules.sql
12_course_modules.sql
13_skills.sql
13b_course_skills.sql
14_syllabus.sql
15_course_reviews.sql          ← تحتاج user id=1 موجود أول
```

## ملاحظات مهمة

1. **course_reviews** تستخدم `user_id=1` — لازم تضيف admin user بـ id=1 قبل تشغيلها
2. **users** — الـ password مش real hash، بدك تغيره أو تعمل reset
3. **syllabus** — مقسَّم حسب topics من الداتاسيت (max 50 topic per course)
4. **domain_id في courses** — مبني على عمود `keyword` من الداتاسيت
5. جداول فاضية (بتحتاج بيانات يدوية أو NLP pipeline):
   - assessments, questions, answer_options
   - aptitude_score_mappings
   - student_skill_matrices
   - recommendation_logs

## إحصائيات

| الجدول | الصفوف |
|---|---|
| course_levels | 3 |
| course_types | 5 |
| syllabus_types | 6 |
| domains | 12 |
| organizations | 193 |
| users (instructors) | 1,376 |
| instructor_profiles | 1,376 |
| courses | 2,228 |
| course_organizations | 2,299 |
| course_instructors | 3,544 |
| learning_outcomes | 1,742 |
| modules | 2,703 |
| course_modules | 2,703 |
| skills | 3,988 |
| course_skills | 3,988 |
| syllabus | ~73,826 |
| course_reviews | 2,228 |
| **المجموع** | **~100,000+** |
