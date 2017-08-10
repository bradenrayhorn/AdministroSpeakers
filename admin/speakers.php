<?php
    // Load speakers
    $administro->plugins['Speakers']->loadSpeakers();
    $speakers = $administro->plugins['Speakers']->speakers;
    // Generate form nonce
    $addSpeakerNonce = $administro->generateNonce('addspeaker');
    $deleteSpeakerNonce = $administro->generateNonce('deletespeaker');
    $uploadNonce = $administro->generateNonce('speakerpresentation');
?>
<div class='title'>
    Speakers
</div>
<div class='spacer'></div>
<?php
    ksort($speakers);
    foreach($speakers as $date => $speaker) {
        $speakerDate = new DateTime('second Friday of ' . $date);
        $year = $speakerDate->format('Y');
        $month = $speakerDate->format('F');
        $monthNum = $speakerDate->format('m');

        $topic = '';
        if(!empty($speaker['topic'])) {
            $topic = ' - ' . $speaker['topic'];
        }

        $presentation = '';
        if($speaker['presentation'] !== false) {
            $presentation = ' [<a href="' . $administro->baseDir . 'speakerfile/' . $speaker['presentation'] . '">Presentation</a>]';
        }

        $delLink = ' <a class="delLink" href="' . $administro->baseDir . 'form/deletespeaker?nonce=' . $deleteSpeakerNonce;
        $delLink .= '&speaker=' . $date . '"><i class="fa fa-times"></i></a>';

        echo '<div><b>' . $month . ' ' . $year . ': </b>' . $speaker['name'] . $topic . $presentation . $delLink . '</div>';
    }
?>
<div class='title sub'>
    Add Speaker
</div>
<div>
    <form method='post' action='<?php echo $administro->baseDir . 'form/addspeaker' ?>'>
        <div class='row'>
            <div class='two columns'>
                <label>Name</label>
                <input class="u-full-width" type="text" name='name' required>
            </div>
            <div class='two columns'>
                <label>Topic</label>
                <input class="u-full-width" type="text" name='topic'>
            </div>
        </div>
        <div class='row'>
            <div class='two columns'>
                <label>Month</label>
                <select name='month'>
                    <option value='01'>January</option>
                    <option value='02'>February</option>
                    <option value='03'>March</option>
                    <option value='04'>April</option>
                    <option value='05'>May</option>
                    <option value='06'>June</option>
                    <option value='07'>July</option>
                    <option value='08'>August</option>
                    <option value='09'>September</option>
                    <option value='10'>October</option>
                    <option value='11'>November</option>
                    <option value='12'>December</option>
                </select>
            </div>
            <div class='two columns'>
                <label>Year</label>
                <select name='year'>
                    <?php
                        $year = date("Y");
                        for($y = $year; $y < ($year + 5); $y++) {
                            echo '<option value="' . $y . '">' . $y . '</option>';
                        }
                    ?>
                </select>
            </div>
        </div>
        <input type='hidden' name='nonce' value='<?php echo $addSpeakerNonce; ?>'>
        <input class="button-primary" type="submit" value="Add Speaker">
    </form>
</div>
<!-- Presentation -->
<div class='title sub'>
    Add Presentation
</div>
<form method='post' action='<?php echo $administro->baseDir . 'form/speakerpresentation' ?>' enctype='multipart/form-data'>
    <div class='row'>
        <label>Speaker</label>
        <select name='speaker'>
            <?php
                foreach($speakers as $date => $speaker) {
                    $speakerDate = new DateTime('second Friday of ' . $date);
                    $year = $speakerDate->format('Y');
                    $month = $speakerDate->format('F');
                    $monthNum = $speakerDate->format('m');

                    echo '<option value="' . $year . '-' . $monthNum . '">' . $speaker['name'] . ' (' . $month . ' ' . $year . ')</option>';
                }
            ?>
        </select>
    </div>
    <div class='row'>
        <label>File</label>
        <input type='file' name='file'>
    </div>
    <input type='hidden' name='nonce' value='<?php echo $uploadNonce; ?>'>
    <input class="button-primary" type="submit" value="Add Presentation">
</form>
<style>
    .delLink {
        color: black;
        text-decoration: none;
    }
    .delLink:hover {
        color: black;
        text-decoration: underline;
    }
</style>
