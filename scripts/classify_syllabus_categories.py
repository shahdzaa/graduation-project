"""Classify each course module into a semantic category.

Inputs (relative to the Laravel project root):
  storage/app/imports/course_structure.json
  storage/app/imports/afterclean.csv

Outputs:
  storage/app/imports/category_taxonomy.json
  storage/app/imports/module_category_assignments.json

Run this file from the Laravel project root:
  python scripts/classify_syllabus_categories.py
"""

from __future__ import annotations

import csv
import json
from collections import Counter, defaultdict
from pathlib import Path

import numpy as np
from sentence_transformers import SentenceTransformer


PROJECT_ROOT = Path(__file__).resolve().parents[1]
IMPORT_DIR = PROJECT_ROOT / "storage" / "app" / "imports"
STRUCTURE_FILE = IMPORT_DIR / "course_structure.json"
CLEAN_DATASET_FILE = IMPORT_DIR / "afterclean.csv"
TAXONOMY_OUTPUT = IMPORT_DIR / "category_taxonomy.json"
ASSIGNMENTS_OUTPUT = IMPORT_DIR / "module_category_assignments.json"

MODEL_NAME = "all-MiniLM-L6-v2"
FALLBACK_THRESHOLD = 0.23


def category(slug: str, name: str, description: str, *, general: bool = False) -> dict:
    return {
        "slug": slug,
        "name": name,
        "description": description,
        "general": general,
    }


TAXONOMY = {
    "Computer Science": [
        category(
            "computer-science-programming-fundamentals",
            "Programming Fundamentals",
            "Programming basics, variables, data types, control flow, loops, functions, debugging, object-oriented programming, and general coding concepts.",
        ),
        category(
            "computer-science-algorithms-data-structures",
            "Algorithms and Data Structures",
            "Algorithms, complexity, arrays, lists, stacks, queues, trees, graphs, searching, sorting, recursion, dynamic programming, and computational problem solving.",
        ),
        category(
            "computer-science-software-engineering",
            "Software Engineering",
            "Software design, requirements, testing, architecture, design patterns, code quality, version control, agile development, and software project practices.",
        ),
        category(
            "computer-science-web-development",
            "Web Development",
            "HTML, CSS, JavaScript, frontend, backend, web frameworks, APIs, HTTP, websites, web applications, React, Angular, Node, PHP, and web services.",
        ),
        category(
            "computer-science-mobile-app-development",
            "Mobile and Application Development",
            "Mobile apps, Android, iOS, Flutter, application development, desktop applications, user interfaces, and cross-platform development.",
        ),
        category(
            "computer-science-databases-information-management",
            "Databases and Information Management",
            "Relational and non-relational databases, SQL, data modeling, schemas, transactions, query processing, database design, and information retrieval.",
        ),
        category(
            "computer-science-artificial-intelligence",
            "Artificial Intelligence and Machine Learning",
            "Artificial intelligence, machine learning, neural networks, deep learning, intelligent agents, natural language processing, computer vision, and predictive models.",
        ),
        category(
            "computer-science-architecture-operating-systems",
            "Computer Architecture and Operating Systems",
            "Computer organization, processors, memory, assembly, digital systems, operating systems, processes, threads, scheduling, filesystems, and concurrency.",
        ),
        category(
            "computer-science-cybersecurity",
            "Cybersecurity and Secure Computing",
            "Secure coding, cryptography, privacy, authentication, authorization, vulnerabilities, malware, application security, and computer security.",
        ),
        category(
            "computer-science-networks-internet",
            "Computer Networks and the Internet",
            "Network protocols, TCP/IP, routing, switching, internet architecture, sockets, wireless networking, network programming, and communication systems.",
        ),
        category(
            "computer-science-cloud-distributed-devops",
            "Cloud, Distributed Systems and DevOps",
            "Cloud platforms, distributed computing, microservices, containers, Docker, Kubernetes, CI/CD, deployment, scalability, reliability, and DevOps.",
        ),
        category(
            "computer-science-hci-graphics",
            "Human-Computer Interaction and Graphics",
            "User experience, user interface design, usability, accessibility, interaction design, computer graphics, visualization, animation, games, Figma, and multimedia.",
        ),
        category(
            "computer-science-theory-languages",
            "Computer Science Theory and Programming Languages",
            "Programming language theory, compilers, automata, formal languages, computability, type systems, semantics, logic in computing, and theoretical computer science.",
        ),
        category(
            "computer-science-general",
            "General Computer Science",
            "Broad or introductory computer science content that does not clearly belong to a more specific computer science category.",
            general=True,
        ),
    ],
    "DataScience": [
        category(
            "data-science-analysis-exploration",
            "Data Analysis and Exploration",
            "Data analysis, exploratory data analysis, data cleaning, data preparation, descriptive analytics, pandas, spreadsheets, and discovering patterns in data.",
        ),
        category(
            "data-science-statistics-probability",
            "Statistics and Probability",
            "Probability, distributions, sampling, estimation, hypothesis testing, regression, statistical inference, uncertainty, and statistical modeling.",
        ),
        category(
            "data-science-machine-learning",
            "Machine Learning",
            "Supervised and unsupervised learning, classification, regression, clustering, feature engineering, model selection, evaluation, and predictive analytics.",
        ),
        category(
            "data-science-deep-learning",
            "Deep Learning and Neural Networks",
            "Neural networks, deep learning, convolutional networks, recurrent networks, transformers, representation learning, and modern neural architectures.",
        ),
        category(
            "data-science-visualization-bi",
            "Data Visualization and Business Intelligence",
            "Charts, dashboards, visual analytics, Tableau, Power BI, reporting, storytelling with data, presentation, and business intelligence.",
        ),
        category(
            "data-science-data-engineering-big-data",
            "Data Engineering and Big Data",
            "Data pipelines, ETL, data warehouses, Spark, Hadoop, distributed data processing, big data platforms, data lakes, and data infrastructure.",
        ),
        category(
            "data-science-databases-sql",
            "Databases and SQL",
            "SQL, relational databases, database queries, data models, joins, database management, analytics databases, and structured data access.",
        ),
        category(
            "data-science-programming-tools",
            "Data Science Programming and Tools",
            "Python, R, NumPy, pandas, Jupyter, programming workflows, packages, APIs, and software tools used for data science.",
        ),
        category(
            "data-science-nlp-text",
            "Natural Language Processing and Text Analytics",
            "Text processing, language models, tokenization, sentiment analysis, information extraction, NLP, transformers, and text mining.",
        ),
        category(
            "data-science-computer-vision",
            "Computer Vision",
            "Image processing, image classification, object detection, visual recognition, convolutional networks, and extracting information from images and video.",
        ),
        category(
            "data-science-responsible-data",
            "Responsible Data and Data Ethics",
            "Data ethics, fairness, bias, privacy, responsible AI, governance, security, transparency, and ethical use of data and models.",
        ),
        category(
            "data-science-experimentation-research",
            "Experimentation and Research Methods",
            "Experimental design, A/B testing, causal inference, research methodology, surveys, measurement, reproducibility, and evidence-based analysis.",
        ),
        category(
            "data-science-general",
            "General Data Science",
            "Broad or introductory data science content that does not clearly belong to a more specific data science category.",
            general=True,
        ),
    ],
    "Information Technology": [
        category(
            "information-technology-fundamentals",
            "IT Fundamentals and Digital Literacy",
            "Basic computing, digital literacy, computer concepts, productivity software, files, applications, internet use, and introductory information technology.",
        ),
        category(
            "information-technology-support-troubleshooting",
            "Technical Support and Troubleshooting",
            "Help desk, customer support, diagnosis, troubleshooting, maintenance, incident resolution, computer repair, and technical support practices.",
        ),
        category(
            "information-technology-networking",
            "Networking and Connectivity",
            "Computer networks, TCP/IP, routing, switching, DNS, wireless, network devices, connectivity, network monitoring, and network administration.",
        ),
        category(
            "information-technology-system-administration",
            "System Administration",
            "Servers, users, permissions, directories, system configuration, command line, scripting, maintenance, monitoring, and administration of IT systems.",
        ),
        category(
            "information-technology-operating-systems",
            "Operating Systems",
            "Windows, Linux, macOS, installation, filesystems, processes, system tools, operating system configuration, and platform management.",
        ),
        category(
            "information-technology-cybersecurity",
            "Cybersecurity and Information Assurance",
            "Information security, access control, threats, vulnerabilities, security operations, incident response, risk, privacy, and protecting IT infrastructure.",
        ),
        category(
            "information-technology-cloud-computing",
            "Cloud Computing",
            "AWS, Azure, Google Cloud, virtual machines, cloud storage, cloud networking, serverless services, cloud architecture, and cloud administration.",
        ),
        category(
            "information-technology-devops-automation",
            "DevOps and IT Automation",
            "Automation, scripting, infrastructure as code, Git, CI/CD, containers, deployment, configuration management, and DevOps operations.",
        ),
        category(
            "information-technology-databases",
            "Databases and Data Management",
            "Database administration, SQL, data storage, backup, recovery, database security, data governance, and enterprise data management.",
        ),
        category(
            "information-technology-enterprise-infrastructure",
            "Enterprise Systems and Infrastructure",
            "Enterprise architecture, servers, virtualization, storage, identity management, directory services, business applications, and IT infrastructure.",
        ),
        category(
            "information-technology-management",
            "IT Service, Project and Product Management",
            "ITIL, service management, project management, product management, governance, change management, operations, teams, and business processes.",
        ),
        category(
            "information-technology-web-infrastructure",
            "Web Platforms and Application Infrastructure",
            "Web servers, hosting, domains, APIs, content management systems, application platforms, WordPress, and managing web-based systems.",
        ),
        category(
            "information-technology-general",
            "General Information Technology",
            "Broad or introductory information technology content that does not clearly belong to a more specific IT category.",
            general=True,
        ),
    ],
    "Math and Logic": [
        category(
            "math-logic-foundations",
            "Mathematical Foundations",
            "Arithmetic, numbers, fractions, equations, basic mathematical reasoning, foundational mathematics, and general quantitative skills.",
        ),
        category(
            "math-logic-algebra",
            "Algebra",
            "Algebraic expressions, equations, inequalities, polynomials, functions, abstract algebra, groups, rings, and algebraic structures.",
        ),
        category(
            "math-logic-calculus-analysis",
            "Calculus and Mathematical Analysis",
            "Limits, derivatives, integrals, sequences, series, differential equations, multivariable calculus, and real or complex analysis.",
        ),
        category(
            "math-logic-linear-algebra",
            "Linear Algebra",
            "Vectors, matrices, linear transformations, eigenvalues, vector spaces, systems of equations, and matrix computation.",
        ),
        category(
            "math-logic-probability-statistics",
            "Probability and Statistics",
            "Probability, random variables, distributions, sampling, statistical inference, hypothesis testing, regression, and uncertainty.",
        ),
        category(
            "math-logic-discrete-combinatorics",
            "Discrete Mathematics and Combinatorics",
            "Sets, relations, graphs, counting, combinatorics, recurrence, discrete structures, and mathematics for computer science.",
        ),
        category(
            "math-logic-reasoning",
            "Logic and Critical Reasoning",
            "Formal logic, propositions, predicates, proofs, arguments, critical thinking, deductive reasoning, induction, and logical fallacies.",
        ),
        category(
            "math-logic-geometry-trigonometry",
            "Geometry and Trigonometry",
            "Euclidean geometry, analytic geometry, shapes, measurement, angles, trigonometric functions, and spatial reasoning.",
        ),
        category(
            "math-logic-optimization-operations-research",
            "Optimization and Operations Research",
            "Linear and nonlinear optimization, operations research, decision models, scheduling, networks, constraints, and optimal solutions.",
        ),
        category(
            "math-logic-numerical-computational",
            "Numerical and Computational Mathematics",
            "Numerical methods, approximation, simulation, computational mathematics, numerical linear algebra, algorithms, and scientific computing.",
        ),
        category(
            "math-logic-modeling",
            "Mathematical Modeling",
            "Mathematical models, applied mathematics, dynamical systems, differential models, simulation, and representing real-world systems mathematically.",
        ),
        category(
            "math-logic-number-theory-cryptography",
            "Number Theory and Cryptography",
            "Prime numbers, modular arithmetic, number theory, cryptographic mathematics, coding theory, and mathematical security.",
        ),
        category(
            "math-logic-general",
            "General Mathematics and Logic",
            "Broad mathematical or logical content that does not clearly belong to a more specific mathematics category.",
            general=True,
        ),
    ],
    "Physical Science and Engineering": [
        category(
            "physical-engineering-physics",
            "Physics",
            "Mechanics, thermodynamics, waves, optics, electromagnetism, quantum physics, relativity, experimental physics, and physical laws.",
        ),
        category(
            "physical-engineering-chemistry",
            "Chemistry",
            "Atoms, molecules, chemical reactions, organic and inorganic chemistry, physical chemistry, analytical chemistry, and laboratory chemistry.",
        ),
        category(
            "physical-engineering-materials",
            "Materials Science",
            "Material properties, metals, polymers, ceramics, composites, nanomaterials, crystal structures, processing, and materials characterization.",
        ),
        category(
            "physical-engineering-electrical-electronics",
            "Electrical and Electronics Engineering",
            "Circuits, electronics, signals, semiconductors, digital systems, embedded systems, communication electronics, and electrical design.",
        ),
        category(
            "physical-engineering-mechanical",
            "Mechanical Engineering",
            "Mechanics, machines, thermofluids, heat transfer, mechanical design, dynamics, solid mechanics, and mechanical systems.",
        ),
        category(
            "physical-engineering-civil-structural",
            "Civil and Structural Engineering",
            "Structures, buildings, construction, geotechnical engineering, transportation, infrastructure, surveying, and civil engineering design.",
        ),
        category(
            "physical-engineering-robotics-control",
            "Robotics, Control and Mechatronics",
            "Robotics, control systems, automation, sensors, actuators, mechatronics, autonomous systems, feedback, and motion planning.",
        ),
        category(
            "physical-engineering-energy-power",
            "Energy and Power Systems",
            "Electric power, renewable energy, batteries, grids, energy conversion, nuclear energy, solar, wind, and sustainable power systems.",
        ),
        category(
            "physical-engineering-environment-earth",
            "Environmental and Earth Sciences",
            "Climate, geology, earth systems, oceans, atmosphere, environmental science, natural resources, pollution, and environmental monitoring.",
        ),
        category(
            "physical-engineering-manufacturing-industrial",
            "Manufacturing and Industrial Engineering",
            "Manufacturing processes, production, quality, operations, supply chains, industrial systems, process improvement, and factory automation.",
        ),
        category(
            "physical-engineering-aerospace-space",
            "Aerospace and Space Engineering",
            "Aircraft, spacecraft, aerodynamics, propulsion, orbital mechanics, satellites, aviation, and aerospace systems.",
        ),
        category(
            "physical-engineering-design-cad-simulation",
            "Engineering Design, CAD and Simulation",
            "Computer-aided design, CAD, modeling, simulation, finite elements, prototyping, product design, engineering drawings, and design tools.",
        ),
        category(
            "physical-engineering-chemical-process",
            "Chemical and Process Engineering",
            "Chemical processes, reactors, transport phenomena, process control, separation, fluid processes, and industrial chemical engineering.",
        ),
        category(
            "physical-engineering-sustainability-management",
            "Sustainability and Engineering Management",
            "Sustainable engineering, life-cycle thinking, engineering projects, safety, risk, systems engineering, management, and responsible technology.",
        ),
        category(
            "physical-engineering-general",
            "General Physical Science and Engineering",
            "Broad physical science or engineering content that does not clearly belong to a more specific engineering category.",
            general=True,
        ),
    ],
}


def normalize_text(value: object) -> str:
    return " ".join(str(value or "").replace("\u00a0", " ").split())


def normalized_key(value: object) -> str:
    return normalize_text(value).casefold()


def load_json(path: Path):
    if not path.exists():
        raise FileNotFoundError(f"Required file not found: {path}")
    with path.open("r", encoding="utf-8") as handle:
        return json.load(handle)


def load_course_domains(path: Path) -> dict[str, str]:
    if not path.exists():
        raise FileNotFoundError(f"Required file not found: {path}")

    domains: dict[str, str] = {}
    with path.open("r", encoding="utf-8-sig", newline="") as handle:
        reader = csv.DictReader(handle)
        for row in reader:
            title = normalize_text(row.get("course_title"))
            domain = normalize_text(row.get("keyword"))
            if title and domain:
                domains.setdefault(normalized_key(title), domain)
    return domains


def build_module_context(course: dict, module: dict) -> str:
    course_title = normalize_text(course.get("course_title"))
    module_name = normalize_text(module.get("name"))
    topics = [normalize_text(topic) for topic in module.get("topics", [])]
    skills = [normalize_text(skill) for skill in course.get("skills", [])]

    topics = [topic for topic in topics if topic][:24]
    skills = [skill for skill in skills if skill][:16]

    parts = [
        f"Course: {course_title}",
        f"Module: {module_name}",
    ]
    if topics:
        parts.append("Topics: " + "; ".join(topics))
    if skills:
        parts.append("Skills: " + "; ".join(skills))
    return ". ".join(parts)


def category_prototype(domain: str, item: dict) -> str:
    return (
        f"Domain: {domain}. Category: {item['name']}. "
        f"Meaning and related subjects: {item['description']}"
    )


def write_json(path: Path, value) -> None:
    with path.open("w", encoding="utf-8") as handle:
        json.dump(value, handle, ensure_ascii=False, separators=(",", ":"))


def main() -> None:
    structure = load_json(STRUCTURE_FILE)
    domain_by_course = load_course_domains(CLEAN_DATASET_FILE)

    unknown_domains = sorted(
        {
            domain
            for domain in domain_by_course.values()
            if domain not in TAXONOMY
        }
    )
    if unknown_domains:
        raise RuntimeError(f"Missing taxonomy for domains: {unknown_domains}")

    records_by_domain: dict[str, list[dict]] = defaultdict(list)
    missing_courses: list[str] = []

    for course in structure:
        course_title = normalize_text(course.get("course_title"))
        domain = domain_by_course.get(normalized_key(course_title))
        if not domain:
            missing_courses.append(course_title)
            continue

        for module_index, module in enumerate(course.get("modules", []), start=1):
            records_by_domain[domain].append(
                {
                    "course_title": course_title,
                    "module_order": module_index,
                    "module_name": normalize_text(module.get("name")),
                    "context": build_module_context(course, module),
                }
            )

    if missing_courses:
        preview = ", ".join(missing_courses[:10])
        raise RuntimeError(
            f"Could not resolve domains for {len(missing_courses)} courses. Examples: {preview}"
        )

    print(f"Loading semantic model: {MODEL_NAME}")
    model = SentenceTransformer(MODEL_NAME)

    assignments: list[dict] = []
    category_counts: Counter[str] = Counter()
    fallback_count = 0

    for domain, records in records_by_domain.items():
        categories = TAXONOMY[domain]
        specific_categories = [item for item in categories if not item["general"]]
        general_category = next(item for item in categories if item["general"])

        prototypes = [category_prototype(domain, item) for item in specific_categories]
        prototype_embeddings = model.encode(
            prototypes,
            batch_size=32,
            show_progress_bar=False,
            convert_to_numpy=True,
            normalize_embeddings=True,
        )

        contexts = [record["context"] for record in records]
        context_embeddings = model.encode(
            contexts,
            batch_size=64,
            show_progress_bar=True,
            convert_to_numpy=True,
            normalize_embeddings=True,
        )

        scores = np.matmul(context_embeddings, prototype_embeddings.T)
        best_indices = np.argmax(scores, axis=1)
        best_scores = np.max(scores, axis=1)

        for record, best_index, best_score in zip(
            records, best_indices.tolist(), best_scores.tolist()
        ):
            selected = specific_categories[best_index]
            used_fallback = best_score < FALLBACK_THRESHOLD
            if used_fallback:
                selected = general_category
                fallback_count += 1

            category_counts[selected["slug"]] += 1
            assignments.append(
                {
                    "course_title": record["course_title"],
                    "module_order": record["module_order"],
                    "module_name": record["module_name"],
                    "domain": domain,
                    "category_slug": selected["slug"],
                    "confidence": round(float(best_score), 6),
                    "used_fallback": used_fallback,
                }
            )

    taxonomy_output = []
    for domain, categories in TAXONOMY.items():
        taxonomy_output.append(
            {
                "domain": domain,
                "categories": [
                    {
                        "slug": item["slug"],
                        "name": item["name"],
                        "description": item["description"],
                        "general": item["general"],
                        "order_index": index,
                    }
                    for index, item in enumerate(categories, start=1)
                ],
            }
        )

    write_json(TAXONOMY_OUTPUT, taxonomy_output)
    write_json(ASSIGNMENTS_OUTPUT, assignments)

    print(f"Modules classified: {len(assignments)}")
    print(f"Fallback assignments: {fallback_count}")
    print(f"Taxonomy file: {TAXONOMY_OUTPUT}")
    print(f"Assignments file: {ASSIGNMENTS_OUTPUT}")
    print("Category distribution:")
    for slug, count in sorted(category_counts.items()):
        print(f"  {slug}: {count}")


if __name__ == "__main__":
    main()
