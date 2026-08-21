# Backend review

## Main database alignment fixes

- Removed the course-to-category relationship and every `category_id` validation from courses.
- Categories now expose syllabus counts and use the paginated `GET /api/categories/{category}/syllabi` endpoint.
- Skills are global and no longer reference modules or aptitude mappings.
- The user relationship is now `placementAttempts`; the removed assessment tables are no longer routed.
- Recommendation logs validate `attempt_id` against `placement_attempts`.
- Student course fields now match `user_id`, `enrolled_at`, `status`, and `progress_percent`.
- Student skill matrix fields now match `user_id`, `skill_id`, `current_score`, and `last_updated`.

## Query improvements

- Large collections use pagination with bounded `per_page` values.
- Course lists load only domain, level, and type; curriculum is loaded only for a single course.
- Syllabus lists are paginated instead of loading all syllabus rows.
- Lookup endpoints use `withCount()` instead of loading all related courses or syllabus rows.
- Student, notification, certificate, review, and recommendation queries are scoped to the authenticated user.
- Placement answer submission validates options in batches and writes answers using `upsert()`.
- Placement syllabus selection no longer creates duplicate rows through course joins.
- A migration adds indexes for the main filters and sorting paths.

## Changed relation-management routes

The following pivot tables use composite primary keys, so delete operations now use both keys:

- `DELETE /api/course-instructors/{course}/{user}`
- `DELETE /api/course-organizations/{course}/{organization}`
- `DELETE /api/course-skills/{course}/{skill}`

## Placement routes

- `POST /api/placement/generate`
- `POST /api/placement/{attemptId}/submit`
- `GET /api/placement-attempts`
- `GET /api/placement-attempts/{attempt}`

## After copying the files into the Laravel project

```powershell
powershell -ExecutionPolicy Bypass -File .\cleanup-legacy-assessment-files.ps1
composer dump-autoload
php artisan optimize:clear
php artisan migrate
php artisan route:list --path=api
php artisan test
```

The new migration only adds query indexes. Do not run `migrate:fresh` for this update.

The cleanup script removes only the old assessment controllers, resources, and duplicate placement model that no longer have database tables.
