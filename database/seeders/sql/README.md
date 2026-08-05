# Masar Recommendation — Database Seeders (v2, من الداتا النضيفة)
مولَّدة من `clean_courses.csv` (1130 كورس نضيف — بعد فلترة تطابق العدد وتطابق المحتوى)

## ترتيب التشغيل (نفس ترتيب الملفات الأصلية)
```
01_course_levels.sql
02_course_types.sql
03_syllabus_types.sql
04_domains.sql
05_organizations.sql
06_users_instructors.sql
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
15_course_reviews.sql
```
أو شغّل `00_RUN_ALL.sql` مباشرة (فيه `SET FOREIGN_KEY_CHECKS=0/1` حواليه).

## إحصائيات

| الجدول | الصفوف |
|---|---|
| course_levels | 3 |
| course_types | 5 |
| syllabus_types | 6 |
| domains | 12 |
| organizations | 138 |
| users (instructors) | 750 |
| instructor_profiles | 750 |
| courses | 1,130 |
| course_organizations | 1,180 |
| course_instructors | 2,097 |
| learning_outcomes | 692 |
| modules | 5,315 |
| course_modules | 5,315 |
| skills | 948 |
| course_skills | 948 |
| syllabus | 33,965 |
| course_reviews | 1,130 |

## قرارات وافتراضات مهمة (فروقات عن الملفات الأصلية)

1. **المصدر**: `clean_courses.csv` بس (1130 كورس) — مش كل الـ2561، ومش الملف القديم 2228.
2. **الـIDs**: كلها جديدة ومتسلسلة من 1، مش امتداد للـIDs القديمة (منظومة منفصلة تمامًا).
3. **modules/syllabus غير مكررة عبر الكورسات**: زي الأصل بالضبط — كل كورس بولّد صفوف modules خاصة فيه (مش شير module بين كورسين حتى لو نفس الاسم)، وربط 1-إلى-1 مع course_modules.
4. **syllabus.type_id و duration_minutes**: لاحظت إنه بالملفات الأصلية *كل* الصفوف كانت `type_id=6` (General) و`duration_minutes=30` بدون استثناء — يعني ما كان في تصنيف فعلي مطبّق. عملت نفس الشي بالضبط (كل الصفوف Generic، 30 دقيقة).
5. **modules.duration_minutes**: نفس الملاحظة — كانت ثابتة 60 بكل الصفوف الأصلية، فطبّقتها ثابتة 60 هون كمان.
6. **skills**: بالملف الأصلي كانت المنطقة معقدة شوي (أحيانًا skill_gain بينقسم لأكتر من صف، خصوصًا لما في نص بين قوسين متل "Idle (Python)"). هون بسّطتها: **صف واحد لكل كورس** يجمع كل عناصر `skill_gain` بمسافة وحدة، مربوط بـ`module_id` أول موديول بالكورس. أبسط وأوضح، بس مش نسخة حرفية 100% من منطق التقسيم الأصلي.
7. **description (بعمود courses)**: مبني من مجموعة كلمات فريدة (deduped) مأخوذة من العنوان + أسماء الموديولات + skill_gain + كل نص الـsyllabus، مع تبسيط بدائي للجمع (singularization بسيط). نفس الفكرة العامة يلي شكلها الملف الأصلي، بس مش نفس الترتيب/الخوارزمية الدقيقة (الأصلية غالبًا استخدمت `set()` بترتيب hash غير قابل لإعادة الإنتاج).
8. **level_id / average_rating**: بعض الكورسات ما فيها `level` أو `rating` بالداتا الجديدة (75 و52 صف على التوالي) — خليتهم `NULL` بدل ما أفرض قيمة افتراضية. الملف الأصلي ما كان فيه أي NULL بهالحقول لأنه مصدره كان مضبوط بالكامل بهالنواحي.
9. **course_reviews.rating**: بدل ما أثبّتها 5 لكل الصفوف زي الأصلي، استخدمت `rating` الفعلي للكورس (مقرّب لأقرب رقم صحيح 1-5)، ولو مفقود رجعت للافتراضي 5. هاد تحسين مقصود، مو تطابق حرفي.
10. **الـemails**: نفس صيغة الملف الأصلي بالضبط (تنضيف الاسم من الرموز، lowercase، ضم بالنقاط، `@masar.edu`)، مع معالجة تصادم أي بريد مكرر بإضافة الـuser id.
11. **password**: نفس الـhash الوهمي المستخدم بالملف الأصلي (لسا لازم تغيّره / تعمل reset، زي ما كانت الملاحظة الأصلية).
12. **جداول فاضية** (زي الأصل): assessments, questions, answer_options, aptitude_score_mappings, student_skill_matrices, recommendation_logs — ما إلها seeder هون.

## ملاحظة على الجودة
هاد الملف مبني من `clean_courses.csv` يلي هو أصلًا فلترة صارمة (تطابق عدد الموديولات مع الـsyllabus + تطابق كلمات مفتاحية بين العنوان/الموديولات ومحتوى الـsyllabus). يعني نظريًا ما المفروض يصير فيه حالات تلوث محتوى زي "Cybersecurity Consultant" يلي كانت بالملف القديم.
