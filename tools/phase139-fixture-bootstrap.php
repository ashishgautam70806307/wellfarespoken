<?php
/**
 * Protected browser-test database fixture.
 * Loaded only by tools/phase136-browser-check.mjs through PHP auto_prepend_file.
 */
class Phase136FixtureStatement extends PDOStatement
{
    private Phase136FixturePDO $fixturePdo;
    private string $sqlText;
    private array $rows = [];
    private int $affected = 0;
    private int $cursor = 0;

    public function __construct(Phase136FixturePDO $pdo, string $sql)
    {
        $this->fixturePdo = $pdo;
        $this->sqlText = $sql;
    }

    public function execute(?array $params = null): bool
    {
        [$this->rows, $this->affected] = $this->fixturePdo->run($this->sqlText, $params ?? []);
        $this->cursor = 0;
        return true;
    }

    public function fetch(int $mode = PDO::FETCH_DEFAULT, int $cursorOrientation = PDO::FETCH_ORI_NEXT, int $cursorOffset = 0): mixed
    {
        return $this->rows[$this->cursor++] ?? false;
    }

    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): array
    {
        if ($mode === PDO::FETCH_COLUMN) {
            $column = (int)($args[0] ?? 0);
            return array_map(static fn(array $row): mixed => array_values($row)[$column] ?? null, $this->rows);
        }
        return $this->rows;
    }

    public function fetchColumn(int $column = 0): mixed
    {
        $row = $this->rows[0] ?? null;
        if (!is_array($row)) return false;
        return array_values($row)[$column] ?? false;
    }

    public function rowCount(): int
    {
        return $this->affected;
    }
}

class Phase136FixturePDO extends PDO
{
    private array $data;
    private bool $transaction = false;
    private int $lastId = 900;

    public function __construct()
    {
        $this->data = $this->fixtures();
    }

    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        return new Phase136FixtureStatement($this, $query);
    }

    public function query(string $query, ?int $fetchMode = null, mixed ...$fetchModeArgs): PDOStatement|false
    {
        $statement = new Phase136FixtureStatement($this, $query);
        $statement->execute();
        return $statement;
    }

    public function exec(string $statement): int|false
    {
        [, $affected] = $this->run($statement, []);
        return $affected;
    }

    public function lastInsertId(?string $name = null): string|false
    {
        return (string)$this->lastId;
    }

    public function beginTransaction(): bool { $this->transaction = true; return true; }
    public function commit(): bool { $this->transaction = false; return true; }
    public function rollBack(): bool { $this->transaction = false; return true; }
    public function inTransaction(): bool { return $this->transaction; }

    public function run(string $sql, array $params): array
    {
        $q = strtolower(preg_replace('/\s+/', ' ', trim($sql)) ?? '');

        if (str_contains($q, 'information_schema.tables') || str_contains($q, 'information_schema.columns')) {
            return [[[0 => 1, 'COUNT(*)' => 1]], 1];
        }
        if (str_starts_with($q, 'alter table') || str_starts_with($q, 'create table')) return [[], 0];
        if (str_starts_with($q, 'insert ') || str_starts_with($q, 'update ') || str_starts_with($q, 'delete ')) {
            $this->lastId++;
            return [[], 1];
        }

        if (str_contains($q, 'from site_settings')) {
            if (str_contains($q, 'where setting_key')) {
                $key = (string)($params[0] ?? '');
                $value = $this->data['settings'][$key] ?? '';
                return [$value === '' ? [] : [[0 => $value, 'setting_value' => $value]], $value === '' ? 0 : 1];
            }
            $rows = [];
            foreach ($this->data['settings'] as $key => $value) $rows[] = ['setting_key' => $key, 'setting_value' => $value];
            return [$rows, count($rows)];
        }
        if (str_contains($q, 'from students')) return [[], 0];
        if (str_contains($q, 'from nav_menus')) return [[], 0];

        if (str_contains($q, 'from hero_banners')) return [$this->filterPublished($this->data['hero_banners']), count($this->data['hero_banners'])];
        if (str_contains($q, 'from course_variants')) return [$this->data['course_variants'], count($this->data['course_variants'])];
        if (str_contains($q, 'from courses')) {
            if (str_contains($q, 'where id')) {
                $id = (int)($params[0] ?? 0);
                $row = array_values(array_filter($this->data['courses'], static fn(array $r): bool => (int)$r['id'] === $id));
                return [$row, count($row)];
            }
            return [$this->data['courses'], count($this->data['courses'])];
        }
        if (str_contains($q, 'from testimonials')) return [$this->data['testimonials'], count($this->data['testimonials'])];
        if (str_contains($q, 'from videos')) return [$this->data['videos'], count($this->data['videos'])];
        if (str_contains($q, 'from gallery_images')) return [$this->data['gallery'], count($this->data['gallery'])];
        if (str_contains($q, 'from faqs')) return [$this->data['faqs'], count($this->data['faqs'])];
        if (str_contains($q, 'from batch_timings')) return [$this->data['batches'], count($this->data['batches'])];
        if (str_contains($q, 'from content_blocks')) {
            $type = (string)($params[0] ?? '');
            $rows = array_values(array_filter($this->data['content_blocks'], static fn(array $row): bool => $type === '' || $row['block_type'] === $type));
            return [$rows, count($rows)];
        }
        if (str_contains($q, 'from form_options')) {
            $group = (string)($params[0] ?? '');
            $rows = array_values(array_filter($this->data['form_options'], static fn(array $row): bool => $row['option_group'] === $group));
            return [$rows, count($rows)];
        }
        if (str_contains($q, 'from faculty_members')) return [$this->data['faculty'], count($this->data['faculty'])];

        if (str_contains($q, 'from weekly_tests')) {
            $rows = $this->data['weekly_tests'];
            $type = null;
            foreach ($params as $param) if (in_array($param, ['basic', 'previous', 'upcoming'], true)) $type = $param;
            if ($type !== null) $rows = array_values(array_filter($rows, static fn(array $row): bool => $row['test_type'] === $type));
            return [$rows, count($rows)];
        }
        if (str_contains($q, 'from weekly_test_questions')) return [$this->data['weekly_questions'], count($this->data['weekly_questions'])];
        if (str_contains($q, 'from weekly_test_attempts')) return [[], 0];
        if (str_contains($q, 'from weekly_test_answers')) return [[], 0];
        if (str_contains($q, 'from weekly_test_winners')) return [[], 0];

        if (str_contains($q, 'from roadmap_groups')) return [$this->data['roadmap_groups'], count($this->data['roadmap_groups'])];
        if (str_contains($q, 'from roadmap_units')) {
            if (str_contains($q, 'where u.id=') || str_contains($q, 'where u.id =')) {
                $id = (int)($params[0] ?? 0);
                $rows = array_values(array_filter($this->data['roadmap_units'], static fn(array $row): bool => (int)$row['id'] === $id));
                return [$rows, count($rows)];
            }
            return [$this->data['roadmap_units'], count($this->data['roadmap_units'])];
        }
        if (str_contains($q, 'count(*) as item_count') && str_contains($q, 'from roadmap_items')) {
            return [[['unit_id' => 1, 'item_count' => 4], ['unit_id' => 2, 'item_count' => 4], ['unit_id' => 3, 'item_count' => 3]], 3];
        }
        if (str_contains($q, 'from roadmap_items')) return [$this->data['roadmap_items'], count($this->data['roadmap_items'])];
        if (str_contains($q, 'from student_roadmap_progress')) return [[], 0];

        if (str_contains($q, 'from material_settings')) return [[], 0];
        if (str_contains($q, 'from material_collections')) {
            if (str_contains($q, 'where id=')) {
                $id = (int)($params[0] ?? 0);
                $rows = array_values(array_filter($this->data['material_collections'], static fn(array $row): bool => (int)$row['id'] === $id));
                return [$rows, count($rows)];
            }
            return [$this->data['material_collections'], count($this->data['material_collections'])];
        }
        if (str_contains($q, 'from material_units')) return [$this->data['material_units'], count($this->data['material_units'])];
        if (str_contains($q, 'from translation_pairs')) {
            if (str_contains($q, 'where id=')) {
                $id = (int)($params[0] ?? 0);
                $rows = array_values(array_filter($this->data['translation_pairs'], static fn(array $row): bool => (int)$row['id'] === $id));
                return [$rows, count($rows)];
            }
            return [$this->data['translation_pairs'], count($this->data['translation_pairs'])];
        }
        if (str_contains($q, 'from material_practice_attempts')) return [[], 0];
        if (str_contains($q, 'from material_assets')) return [[], 0];
        if (str_contains($q, 'from student_activity_logs')) return [[], 0];

        if (str_contains($q, 'count(*)')) return [[[0 => 0, 'COUNT(*)' => 0]], 1];
        return [[], 0];
    }

    private function filterPublished(array $rows): array
    {
        return array_values(array_filter($rows, static fn(array $row): bool => ($row['published'] ?? 'Yes') === 'Yes'));
    }

    private function fixtures(): array
    {
        $now = date('Y-m-d H:i:s');
        return [
            'settings' => [
                'site_name' => 'Well Fare English Spoken',
                'site_tagline' => 'Speak English With Confidence',
                'site_logo' => 'assets/uploads/brand/logo_20260708_164300_66b228d8.png',
                'site_favicon' => 'assets/uploads/brand/wf-favicon.ico',
                'site_pwa_icon_192' => 'assets/uploads/brand/wf-pwa-icon-192.png',
                'site_pwa_icon_180' => 'assets/uploads/brand/wf-pwa-icon-180.png',
                'phone' => '+91 9506617831',
                'whatsapp' => '919506617831',
                'email' => 'wellfareenglishspoken@gmail.com',
                'address' => 'Station Road, Mariahu, Jaunpur, Uttar Pradesh',
                'mobile_short_address' => 'Mariahu, Jaunpur',
                'admission_marquee_text' => 'Admission open — online and offline spoken English batches available.',
                'facebook_url' => 'https://facebook.com/',
                'instagram_url' => 'https://instagram.com/',
                'youtube_url' => 'https://youtube.com/',
                'linkedin_url' => 'https://linkedin.com/',
                'twitter_url' => 'https://x.com/',
                'footer_about' => 'Practical spoken English with a clear roadmap, daily practice and weekly feedback.',
                'contact_office_time' => 'Monday to Saturday, 8 AM to 7 PM',
            ],
            'hero_banners' => [
                ['id'=>1,'page_key'=>'home','title'=>'Speak English With Confidence','subtitle'=>'Learn, practise and improve with a clear daily path.','image_url'=>'assets/uploads/banners/home-banner-speaking-desktop.webp','desktop_image_url'=>'assets/uploads/banners/home-banner-speaking-desktop.webp','mobile_image_url'=>'assets/uploads/banners/home-banner-speaking-mobile.webp','primary_label'=>'Start Learning','primary_url'=>'learning-roadmap.php','secondary_label'=>'View Courses','secondary_url'=>'courses.php','show_content'=>'Yes','content_position'=>'left','overlay_strength'=>62,'sort_order'=>1,'published'=>'Yes'],
                ['id'=>2,'page_key'=>'home','title'=>'Join Live Online Classes','subtitle'=>'Choose a suitable batch and practise directly with a teacher.','image_url'=>'assets/uploads/banners/home-banner-online-class-desktop.webp','desktop_image_url'=>'assets/uploads/banners/home-banner-online-class-desktop.webp','mobile_image_url'=>'assets/uploads/banners/home-banner-online-class-mobile.webp','primary_label'=>'View Online Classes','primary_url'=>'online-class.php','secondary_label'=>'Admission','secondary_url'=>'admission.php','show_content'=>'Yes','content_position'=>'left','overlay_strength'=>58,'sort_order'=>2,'published'=>'Yes'],
            ],
            'courses' => [
                ['id'=>1,'title'=>'Basic Spoken English','slug'=>'basic-spoken-english','short_description'=>'Build daily-use sentences and speaking confidence.','full_description'=>'Beginner-friendly spoken English course.','duration'=>'3 Months','level'=>'Basic','price'=>'2500','icon'=>'fa-solid fa-seedling','image_url'=>'assets/uploads/courses/course-20260704-174059-c3ef4f4c.png','sort_order'=>1,'published'=>'Yes'],
                ['id'=>2,'title'=>'Intermediate English','slug'=>'intermediate-english','short_description'=>'Improve tense, translation and daily conversation.','full_description'=>'Intermediate speaking and grammar practice.','duration'=>'4 Months','level'=>'Intermediate','price'=>'3500','icon'=>'fa-solid fa-comments','image_url'=>'','sort_order'=>2,'published'=>'Yes'],
                ['id'=>3,'title'=>'Interview English','slug'=>'interview-english','short_description'=>'Prepare professional answers and confident introductions.','full_description'=>'Interview and workplace communication.','duration'=>'6 Weeks','level'=>'Advanced','price'=>'3000','icon'=>'fa-solid fa-briefcase','image_url'=>'','sort_order'=>3,'published'=>'Yes'],
            ],
            'course_variants' => [['id'=>1,'course_id'=>1,'variant_name'=>'Morning Batch','duration'=>'3 Months','price'=>'2500','sort_order'=>1]],
            'testimonials' => [
                ['id'=>1,'student_name'=>'Aman','course_name'=>'Basic Spoken English','review_text'=>'The roadmap helped me practise every day.','rating'=>5,'image_url'=>'','reviewer_role'=>'Student','review_date'=>'July 2026','source_label'=>'Institute','avatar_initials'=>'AM','sort_order'=>1,'published'=>'Yes'],
                ['id'=>2,'student_name'=>'Neha','course_name'=>'Online Class','review_text'=>'Live correction improved my confidence.','rating'=>5,'image_url'=>'','reviewer_role'=>'Student','review_date'=>'July 2026','source_label'=>'Institute','avatar_initials'=>'NS','sort_order'=>2,'published'=>'Yes'],
                ['id'=>3,'student_name'=>'Ravi','course_name'=>'Interview English','review_text'=>'I can now answer interview questions clearly.','rating'=>5,'image_url'=>'','reviewer_role'=>'Student','review_date'=>'June 2026','source_label'=>'Institute','avatar_initials'=>'RK','sort_order'=>3,'published'=>'Yes'],
                ['id'=>4,'student_name'=>'Pooja','course_name'=>'Basic Spoken English','review_text'=>'The practice materials are simple and useful.','rating'=>5,'image_url'=>'','reviewer_role'=>'Student','review_date'=>'June 2026','source_label'=>'Institute','avatar_initials'=>'PV','sort_order'=>4,'published'=>'Yes'],
            ],
            'videos' => [],
            'gallery' => [
                ['id'=>1,'title'=>'Classroom Practice','caption'=>'Students practising spoken English.','image_url'=>'assets/uploads/gallery/gallery-20260621-200631-a4ba4e65.jpg','image_alt'=>'Classroom practice','sort_order'=>1,'published'=>'Yes'],
                ['id'=>2,'title'=>'Speaking Activity','caption'=>'Daily speaking activity.','image_url'=>'assets/uploads/gallery/gallery-20260621-200314-5a90318d.jpg','image_alt'=>'Speaking activity','sort_order'=>2,'published'=>'Yes'],
            ],
            'faqs' => [
                ['id'=>1,'question'=>'Can a complete beginner join?','answer'=>'Yes. Zero-level and basic students can start with guided practice.','sort_order'=>1,'published'=>'Yes'],
                ['id'=>2,'question'=>'Are online classes available?','answer'=>'Yes. Select an online batch and submit the admission form.','sort_order'=>2,'published'=>'Yes'],
                ['id'=>3,'question'=>'How do weekly tests work?','answer'=>'Basic, previous and upcoming tests are managed separately.','sort_order'=>3,'published'=>'Yes'],
            ],
            'batches' => [
                ['id'=>1,'batch_name'=>'Morning Online Batch','course_name'=>'Basic Spoken English','timing'=>'7:00 AM - 8:00 AM','days'=>'Mon to Sat','seats_note'=>'Limited seats','sort_order'=>1,'published'=>'Yes'],
                ['id'=>2,'batch_name'=>'Evening Online Batch','course_name'=>'Intermediate English','timing'=>'6:00 PM - 7:00 PM','days'=>'Mon to Sat','seats_note'=>'Admissions open','sort_order'=>2,'published'=>'Yes'],
            ],
            'content_blocks' => [
                ['id'=>1,'block_type'=>'online_class_feature','title'=>'Live Teacher','subtitle'=>'Learn directly with a teacher.','body'=>'','icon'=>'fa-solid fa-video','sort_order'=>1,'published'=>'Yes'],
                ['id'=>2,'block_type'=>'online_class_feature','title'=>'Speaking Practice','subtitle'=>'Speak and receive correction.','body'=>'','icon'=>'fa-solid fa-microphone-lines','sort_order'=>2,'published'=>'Yes'],
                ['id'=>3,'block_type'=>'admission_benefit','title'=>'Beginner Friendly','subtitle'=>'Start at the right level.','body'=>'','icon'=>'fa-solid fa-seedling','sort_order'=>1,'published'=>'Yes'],
                ['id'=>4,'block_type'=>'home_feature','title'=>'Clear Roadmap','subtitle'=>'Know what to learn next.','body'=>'','icon'=>'fa-solid fa-route','sort_order'=>1,'published'=>'Yes'],
            ],
            'form_options' => [
                ['id'=>1,'option_group'=>'current_level','option_label'=>'Zero Level','option_value'=>'Zero Level','helper_text'=>'','sort_order'=>1,'published'=>'Yes'],
                ['id'=>2,'option_group'=>'current_level','option_label'=>'Basic','option_value'=>'Basic','helper_text'=>'','sort_order'=>2,'published'=>'Yes'],
                ['id'=>3,'option_group'=>'preferred_time','option_label'=>'Morning','option_value'=>'Morning','helper_text'=>'','sort_order'=>1,'published'=>'Yes'],
            ],
            'faculty' => [['id'=>1,'faculty_name'=>'Spoken English Trainer','designation'=>'Lead Trainer','experience'=>'7+ Years','qualification'=>'MA, B.Ed','short_bio'=>'Practical spoken English trainer.','full_bio'=>'','expertise'=>'Conversation, Grammar','image_url'=>'','phone'=>'','email'=>'','sort_order'=>1,'published'=>'Yes']],
            'weekly_tests' => [
                ['id'=>11,'title'=>'Basic Spoken Test','test_type'=>'basic','requires_login'=>'No','status'=>'active','published'=>'Yes','instructions'=>'Answer simple daily-use questions.','duration_minutes'=>20,'total_questions'=>10,'total_marks'=>10,'question_count'=>10,'ready_now'=>1,'starts_at'=>null,'ends_at'=>null,'batch_label'=>'','batch_name'=>'','created_at'=>$now,'shuffle_questions'=>'No','shuffle_options'=>'No'],
                ['id'=>12,'title'=>'Previous Weekly Test','test_type'=>'previous','requires_login'=>'No','status'=>'active','published'=>'Yes','instructions'=>'Practise an earlier paper.','duration_minutes'=>25,'total_questions'=>10,'total_marks'=>10,'question_count'=>10,'ready_now'=>1,'starts_at'=>null,'ends_at'=>null,'batch_label'=>'','batch_name'=>'','created_at'=>$now,'shuffle_questions'=>'No','shuffle_options'=>'No'],
                ['id'=>13,'title'=>'Upcoming Weekly Test','test_type'=>'upcoming','requires_login'=>'Yes','status'=>'active','published'=>'Yes','instructions'=>'Official weekly test.','duration_minutes'=>30,'total_questions'=>10,'total_marks'=>10,'question_count'=>10,'ready_now'=>1,'starts_at'=>null,'ends_at'=>null,'batch_label'=>'Morning Online Batch','batch_name'=>'Morning Online Batch','created_at'=>$now,'shuffle_questions'=>'No','shuffle_options'=>'No'],
            ],
            'weekly_questions' => array_map(static fn(int $id): array => ['id'=>$id,'test_id'=>11,'question_type'=>'hindi_to_english','topic_name'=>'Daily English','level'=>'Beginner','question_text'=>'मैं रोज अंग्रेजी बोलता हूँ।','expected_answer'=>'I speak English every day.','option_a'=>'','option_b'=>'','option_c'=>'','option_d'=>'','marks'=>1,'sort_order'=>$id,'published'=>'Yes','status_deleted'=>0], range(1, 10)),
            'roadmap_groups' => [
                ['id'=>1,'title'=>'Foundation','subtitle'=>'Build basic speaking confidence.','icon'=>'fa-solid fa-seedling','color'=>'#1f5c9f','sort_order'=>1,'published'=>'Yes','status_deleted'=>0],
                ['id'=>2,'title'=>'Daily Speaking','subtitle'=>'Use English in real situations.','icon'=>'fa-solid fa-comments','color'=>'#d8a62d','sort_order'=>2,'published'=>'Yes','status_deleted'=>0],
            ],
            'roadmap_units' => [
                ['id'=>1,'group_id'=>1,'title'=>'Daily Words','subtitle'=>'Common daily-use words.','unit_type'=>'meaning','icon'=>'fa-solid fa-language','points'=>10,'sort_order'=>1,'published'=>'Yes','status_deleted'=>0,'group_title'=>'Foundation','group_icon'=>'fa-solid fa-seedling','group_color'=>'#1f5c9f'],
                ['id'=>2,'group_id'=>1,'title'=>'Basic Sentences','subtitle'=>'Make short correct sentences.','unit_type'=>'lesson','icon'=>'fa-solid fa-book-open-reader','points'=>15,'sort_order'=>2,'published'=>'Yes','status_deleted'=>0,'group_title'=>'Foundation','group_icon'=>'fa-solid fa-seedling','group_color'=>'#1f5c9f'],
                ['id'=>3,'group_id'=>2,'title'=>'Conversation Practice','subtitle'=>'Speak in daily situations.','unit_type'=>'lesson','icon'=>'fa-solid fa-comments','points'=>20,'sort_order'=>1,'published'=>'Yes','status_deleted'=>0,'group_title'=>'Daily Speaking','group_icon'=>'fa-solid fa-comments','group_color'=>'#d8a62d'],
            ],
            'roadmap_items' => [
                ['id'=>1,'unit_id'=>1,'item_key'=>'hello','col_1'=>'Hello','col_2'=>'नमस्ते','col_3'=>'','col_4'=>'','col_5'=>'','col_6'=>'','example_text'=>'Hello, how are you?','sort_order'=>1,'published'=>'Yes','status_deleted'=>0],
                ['id'=>2,'unit_id'=>1,'item_key'=>'thank-you','col_1'=>'Thank you','col_2'=>'धन्यवाद','col_3'=>'','col_4'=>'','col_5'=>'','col_6'=>'','example_text'=>'Thank you for your help.','sort_order'=>2,'published'=>'Yes','status_deleted'=>0],
                ['id'=>3,'unit_id'=>3,'item_key'=>'i-am-ready','col_1'=>'I am ready','col_2'=>'मैं तैयार हूँ','col_3'=>'','col_4'=>'','col_5'=>'','col_6'=>'','example_text'=>'I am ready for class.','sort_order'=>1,'published'=>'Yes','status_deleted'=>0],
                ['id'=>4,'unit_id'=>3,'item_key'=>'i-am-learning','col_1'=>'I am learning','col_2'=>'मैं सीख रहा हूँ','col_3'=>'','col_4'=>'','col_5'=>'','col_6'=>'','example_text'=>'I am learning English.','sort_order'=>2,'published'=>'Yes','status_deleted'=>0],
            ],
            'material_collections' => [['id'=>1,'title'=>'Daily Spoken Practice','slug'=>'daily-spoken-practice','description'=>'Daily-use translation and speaking practice.','level'=>'Beginner','practice_priority'=>10,'sort_order'=>1,'published'=>'Yes','status_deleted'=>0]],
            'material_units' => [['id'=>1,'collection_id'=>1,'title'=>'Daily Conversation','level'=>'Beginner','instructions'=>'Listen, speak and check your answer.','practice_priority'=>10,'sort_order'=>1,'published'=>'Yes','status_deleted'=>0]],
            'translation_pairs' => [
                ['id'=>1,'collection_id'=>1,'unit_id'=>1,'hindi_text'=>'मैं रोज अंग्रेजी बोलता हूँ।','english_text'=>'I speak English every day.','roman_text'=>'Main roz English bolta hoon.','tense_name'=>'Present Simple','situation_tag'=>'Daily Speaking','level'=>'Beginner','explanation'=>'Use present simple for habits.','accepted_english_answers'=>'I speak English daily.','accepted_hindi_answers'=>'','answer_match_mode'=>'smart','sentence_type'=>'Simple','difficulty_level'=>'Easy','common_mistakes'=>'I am speak English.','teacher_hint'=>'Use the base verb speak.','practice_priority'=>10,'sort_order'=>1,'published'=>'Yes','status_deleted'=>0],
                ['id'=>2,'collection_id'=>1,'unit_id'=>1,'hindi_text'=>'मैं तैयार हूँ।','english_text'=>'I am ready.','roman_text'=>'Main taiyar hoon.','tense_name'=>'Is Am Are','situation_tag'=>'Daily Speaking','level'=>'Beginner','explanation'=>'Use am with I.','accepted_english_answers'=>'','accepted_hindi_answers'=>'','answer_match_mode'=>'smart','sentence_type'=>'Simple','difficulty_level'=>'Easy','common_mistakes'=>'I is ready.','teacher_hint'=>'Use am with I.','practice_priority'=>9,'sort_order'=>2,'published'=>'Yes','status_deleted'=>0],
            ],
        ];
    }
}

$GLOBALS['APP_DB_OVERRIDE'] = new Phase136FixturePDO();
putenv('APP_REMOTE_FONTS=false');
putenv('APP_ALLOW_SCHEMA_UPDATES=false');
putenv('APP_DEBUG=false');
putenv('APP_RUNTIME_MODE=local');
