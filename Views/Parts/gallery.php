<?php

$this->RequireScript('https://code.jquery.com/jquery-3.1.0.min.js');
$this->RequireStyle('/CurrentTheme/gallery.css');

$this->StartSection('ExtraHeadElements');
?>

<script>
    function loadImages(){
        $.get("//<?=$_SERVER['HTTP_HOST']?>/gallery/GetImages?user=someone&album=great_pics", function(data){
            var gallery = $('#gallery');
            $.each(data.urls, function(index, url){
                gallery.append("<img src=\"" + url + "\">");
            });
        }, "json");
    }

    $(document).ready(function() {
        loadImages();
    });
</script>

<?php $this->EndSection();


////////////////////////////////////


$this->StartSection('MainContent'); ?>

<div id="gallery-container">
    <div id="gallery">

    </div>
</div>

<?php $this->EndSection(); ?>
