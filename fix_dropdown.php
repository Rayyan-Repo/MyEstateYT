<?php
$base = __DIR__ . '/projectV2/';
$files = ['home.php','listings.php','about.php','contact.php','saved.php','requests.php','upcoming.php','view_property.php','search.php'];

// The old hover+click JS block
$old = "  navUser.addEventListener('mouseenter',()=>menu.classList.add('open'));\r\n  navUser.addEventListener('mouseleave',()=>menu.classList.remove('open'));\r\n  navUser.addEventListener('click',(e)=>{e.stopPropagation();menu.classList.toggle('open');});\r\n  document.addEventListener('click',()=>menu.classList.remove('open'));\r\n  window.addEventListener('scroll',()=>menu.classList.remove('open'),{passive:true});";

// New click-only JS block
$new = "  navUser.addEventListener('click',(e)=>{e.stopPropagation();menu.classList.toggle('open');});\r\n  document.addEventListener('click',()=>menu.classList.remove('open'));\r\n  window.addEventListener('scroll',()=>menu.classList.remove('open'),{passive:true});";

foreach($files as $f){
    $path = $base . $f;
    if(!file_exists($path)){ echo "SKIP (not found): $f\n"; continue; }
    $content = file_get_contents($path);
    $new_content = str_replace($old, $new, $content);
    // Also remove CSS hover rule that some pages use instead of JS
    $new_content = str_replace(".nav-user:hover .nav-drop-menu{display:block;}", "", $new_content);
    file_put_contents($path, $new_content);
    $changed = ($new_content !== $content) ? ' [FIXED]' : ' [no change]';
    echo $f . $changed . "\n";
}
echo "\nDone!";
?>
