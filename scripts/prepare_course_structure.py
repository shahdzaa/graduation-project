import ast
import csv
import json
import sys
from pathlib import Path


PROJECT_ROOT = Path(__file__).resolve().parents[1]

INPUT_FILE = (
    PROJECT_ROOT
    / "storage"
    / "app"
    / "imports"
    / "extended_it_courses_english_only.csv"
)

OUTPUT_FILE = (
    PROJECT_ROOT
    / "storage"
    / "app"
    / "imports"
    / "course_structure.json"
)


def normalize_text(value):
    if value is None:
        return ""

    return " ".join(
        str(value)
        .replace("\xa0", " ")
        .split()
    )


def parse_literal(value):
    value = normalize_text(value)

    if not value:
        return []

    try:
        parsed = ast.literal_eval(value)
    except (ValueError, SyntaxError):
        return []

    if isinstance(parsed, (list, tuple)):
        return list(parsed)

    return [parsed]


def flatten_strings(value):
    result = []

    if isinstance(value, (list, tuple)):
        for item in value:
            result.extend(flatten_strings(item))
    else:
        text = normalize_text(value)

        if text:
            result.append(text)

    return result


def unique_preserving_order(values):
    result = []
    seen = set()

    for value in values:
        value = normalize_text(value)
        key = value.casefold()

        if not value or key in seen:
            continue

        seen.add(key)
        result.append(value)

    return result


def build_module_structure(modules, syllabus):
    module_names = unique_preserving_order(
        flatten_strings(modules)
    )

    syllabus_groups = (
        syllabus
        if isinstance(syllabus, list)
        else []
    )

    if not module_names and not syllabus_groups:
        return []

    if not module_names:
        return [
            {
                "name": "Course Content",
                "topics": unique_preserving_order(
                    flatten_strings(syllabus_groups)
                ),
            }
        ]

    result = [
        {
            "name": module_name,
            "topics": [],
        }
        for module_name in module_names
    ]

    if not syllabus_groups:
        return result

    if len(module_names) == len(syllabus_groups):
        for index, syllabus_group in enumerate(
            syllabus_groups
        ):
            result[index]["topics"] = (
                unique_preserving_order(
                    flatten_strings(syllabus_group)
                )
            )

        return result

    if len(module_names) == 1:
        result[0]["topics"] = (
            unique_preserving_order(
                flatten_strings(syllabus_groups)
            )
        )

        return result

    for index, syllabus_group in enumerate(
        syllabus_groups
    ):
        target_index = min(
            index,
            len(result) - 1
        )

        result[target_index]["topics"].extend(
            flatten_strings(syllabus_group)
        )

    for module in result:
        module["topics"] = unique_preserving_order(
            module["topics"]
        )

    return result

def structure_score(modules, syllabus, skills):
    module_names = unique_preserving_order(
        flatten_strings(modules)
    )

    syllabus_groups = (
        syllabus
        if isinstance(syllabus, list)
        else []
    )

    if module_names and syllabus_groups:
        if len(module_names) == len(syllabus_groups):
            return 5

        if len(module_names) == 1:
            return 4

        return 3

    if module_names:
        return 2

    if syllabus_groups:
        return 1

    return 0

def main():
    if not INPUT_FILE.exists():
        raise FileNotFoundError(
            f"Input file not found: {INPUT_FILE}"
        )

    csv.field_size_limit(
        min(sys.maxsize, 2_147_483_647)
    )

    selected_courses = {}

    with INPUT_FILE.open(
        "r",
        encoding="utf-8-sig",
        newline="",
    ) as csv_file:
        reader = csv.DictReader(csv_file)

        for row in reader:
            course_title = normalize_text(
                row.get("course_title")
            )

            if not course_title:
                continue

            modules = parse_literal(
                row.get("modules")
            )

            syllabus = parse_literal(
                row.get("syllabus")
            )

            skills = parse_literal(
                row.get("skill_gain")
            )

            score = structure_score(
                modules,
                syllabus,
                skills,
            )

            course_key = course_title.casefold()

            current = selected_courses.get(
                course_key
            )

            if (
                current is not None
                and current["score"] >= score
            ):
                continue

            selected_courses[course_key] = {
                "course_title": course_title,
                "keyword": normalize_text(
                    row.get("keyword")
                ),
                "modules": modules,
                "syllabus": syllabus,
                "skills": skills,
                "score": score,
            }

    output = []

    total_modules = 0
    total_topics = 0
    total_skills = 0

    for selected in selected_courses.values():
        module_structure = build_module_structure(
            selected["modules"],
            selected["syllabus"],
        )

        skills = unique_preserving_order(
            flatten_strings(
                selected["skills"]
            )
        )

        total_modules += len(module_structure)
        total_topics += sum(
            len(module["topics"])
            for module in module_structure
        )
        total_skills += len(skills)

        output.append(
            {
                "course_title":
                    selected["course_title"],
                "keyword":
                    selected["keyword"],
                "modules":
                    module_structure,
                "skills":
                    skills,
            }
        )

    with OUTPUT_FILE.open(
        "w",
        encoding="utf-8",
    ) as json_file:
        json.dump(
            output,
            json_file,
            ensure_ascii=False,
            separators=(",", ":"),
        )

    print(
        f"Courses prepared: {len(output)}"
    )

    print(
        f"Modules prepared: {total_modules}"
    )

    print(
        f"Syllabus topics prepared: {total_topics}"
    )

    print(
        f"Skill links prepared: {total_skills}"
    )

    print(
        f"Output created: {OUTPUT_FILE}"
    )


if __name__ == "__main__":
    main()