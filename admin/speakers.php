<?php
    // Load speakers
    $administro->plugins['Speakers']->loadSpeakers();
    $speakers = $administro->plugins['Speakers']->speakers;
    // Generate form nonce
    $addSpeakerNonce = $administro->generateNonce('addspeaker');
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

        echo '<div><b>' . $month . ' ' . $year . ': </b>' . $speaker['name'] . ' - ' . $speaker['topic'] . '</div>';
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
                <input class="u-full-width" type="text" name='topic' required>
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
