<?php
$root = dirname(__DIR__);
$checks = [];
$add = static function(string $name, bool $ok) use (&$checks): void { $checks[] = [$name,$ok]; };

$pages = [
 'spoken-materials.php' => ['practiceChangeMode','practiceHandsfree','practiceSpeak','practiceStop','practiceAnswer','practiceCheck','data-goal="revision"'],
 'free-ai-english-practice.php' => ['quickPracticeForm','quickMicBtn','lesson-pick-btn','prevQuestionBtn','checkAnswerBtn','nextQuestionBtn','appVoiceBtn'],
 'learning-roadmap.php' => ['roadmapContinueBtn','rm126-open','rm126-locked','roadmapResetProgress','Manage roadmap'],
];
foreach ($pages as $file=>$markers) {
    $s=(string)file_get_contents($root.'/'.$file);
    $add("Phase180 CSS attached $file", str_contains($s,'phase180-old-design-mobile-fix.css'));
    $add("No Phase179 redesign attached $file", !str_contains($s,'phase179-mobile-learning-design.css'));
    foreach($markers as $m){$add("$file keeps $m",str_contains($s,$m));}
}
$spoken=(string)file_get_contents($root.'/spoken-materials.php');
$add('Spoken has all four original modes', substr_count($spoken,'class="wf143-mode-card"')===4);
$css=(string)file_get_contents($root.'/assets/css/phase180-old-design-mobile-fix.css');
$add('Phase180 CSS mobile scoped',str_contains($css,'@media (max-width:760px)'));
$add('Phase180 CSS has no feature hide rule',!preg_match('/display\s*:\s*none\s*!important/i',$css));
$add('Phase180 protects short screens',str_contains($css,'max-height:600px'));
$sw=(string)file_get_contents($root.'/sw.js');
$add('Service worker v180 or newer',preg_match('/wellfare-spoken-static-v(18[0-9]|19[0-9]|[2-9][0-9]{2,})/', $sw)===1);
$add('Phase180 CSS precached',str_contains($sw,"./assets/css/phase180-old-design-mobile-fix.css"));
$add('Phase179 CSS not precached',!str_contains($sw,'phase179-mobile-learning-design.css'));
$headerHash=hash_file('sha256',$root.'/includes/header.php');
$footerHash=hash_file('sha256',$root.'/includes/footer.php');
$voiceHash=hash_file('sha256',$root.'/assets/js/phase170-spoken-practice.js');
$add('Common header preserved', $headerHash==='629aa565b26afa711c9fc09217cf0496c22e3a6a18884fb81913abfaf17493d4');
$add('Common footer preserved', $footerHash==='f88794a390616502263af7b11e5c6c90f445bcf1d30bfacc54fd5fdc0fa378bb');
$add('Phase170 voice JS preserved', $voiceHash==='c11e38a04e7bd6e79320ddb61e2e8c4960cbb797ca15550c72cffa1bb22b7b1b');
foreach(['spoken-materials.php','free-ai-english-practice.php','learning-roadmap.php'] as $file){
 $s=(string)file_get_contents($root.'/'.$file);
 $s=str_replace(", 'assets/css/phase180-old-design-mobile-fix.css'",'',$s);
 $s=str_replace("\n\$page_final_styles = ['assets/css/phase180-old-design-mobile-fix.css'];",'',$s);
 $expected=[
  'spoken-materials.php'=>'84e6986ed82fd1dc5dc974286943e051ee03de3ef3f6c4c96b4f9bd0a9503501',
  'free-ai-english-practice.php'=>'848b9e6da9c6d27ef0d21656cce81b6af5b51ec0b1a4042c738d89018741c152',
  'learning-roadmap.php'=>'a1343730ea14b4aee52e87bfd04056a0cca90c234443dc1267583df4b297aa67',
 ];
 $add("$file feature source equals old Phase174", hash('sha256',$s)===$expected[$file]);
}
$failed=array_values(array_filter($checks,fn($c)=>!$c[1]));
foreach($checks as [$name,$ok]) echo ($ok?'PASS':'FAIL')." - $name\n";
echo "TOTAL ".(count($checks)-count($failed))."/".count($checks)." PASS\n";
exit($failed?1:0);
