<?php

    use Symfony\Component\Yaml\Yaml;

    class SpeakersPlugin extends AdministroPlugin {

        var $dataFile, $presentations, $speakers;

        public function configLoaded() {
            // Set file locations
            $this->dataFile = $this->administro->rootDir . 'data/speakers/speakers.yaml';
            $this->presentations = $this->administro->rootDir . 'data/speakers/presentations/';
            // Create directory
            @mkdir($this->presentations, 0777, true);
            // Add speaker file viewer
            $this->administro->reservedRoutes['speakerfile'] = 'plugins/Speakers/speakerroute.php';
            // Add admin page
            $this->administro->adminPages['speakers'] =
                array('icon' => 'microphone', 'name' => 'Speakers', 'file' => 'plugins/Speakers/admin/speakers.php');
            // Add forms
            array_push($this->administro->forms, 'addspeaker');
            // Add variable
            $this->administro->markdownFunctions['speakers'] = 'Speakers:speakerDisplay';
        }

        public function speakerDisplay() {
            if(!isset($this->speakers)) $this->loadSpeakers();
            // Read speakers
            $futureSpeakers = array();
            $pastSpeakers = array();
            foreach($this->speakers as $date => $speaker) {
                $speakerDate = new DateTime('second Friday of ' . $date);
                if($speakerDate < new DateTime()) {
                    $pastSpeakers[$date] = $speaker;
                } else {
                    $futureSpeakers[$date] = $speaker;
                }
            }

            krsort($pastSpeakers);

            $futureHtml = '';
            $pastHtml = '';

            foreach($futureSpeakers as $date => $speaker) {
                $speakerDate = new DateTime('second Friday of ' . $date);
                $year = $speakerDate->format('Y');
                $month = $speakerDate->format('F');
                $topic = '';
                if(!empty($speaker['topic'])) {
                    $topic = ' - ' . $speaker['topic'];
                }
                $presentation = '';
                if($speaker['presentation'] !== false) {
                    $presentation = ' [<a href="' . $this->administro->baseDir . 'speakerfile/' . $speaker['presentation'] . '">Presentation</a>]';
                }
                $futureHtml .= '<p><b>' . $month . ' ' . $year . ': </b>' . $speaker['name'] . $topic . $presentation . '</p>';
            }

            foreach($pastSpeakers as $date => $speaker) {
                $speakerDate = new DateTime('second Friday of ' . $date);
                $year = $speakerDate->format('Y');
                $month = $speakerDate->format('F');
                $topic = '';
                if(!empty($speaker['topic'])) {
                    $topic = ' - ' . $speaker['topic'];
                }
                $presentation = '';
                if($speaker['presentation'] !== false) {
                    $presentation = ' [<a href="' . $this->administro->baseDir . 'speakerfile/' . $speaker['presentation'] . '">Presentation</a>]';
                }
                $pastHtml .= '<p><b>' . $month . ' ' . $year . ': </b>' . $speaker['name'] . $topic . $presentation . '</p>';
            }

            return '<p><h3>Upcoming Speakers</h3></p>' . $futureHtml . '<p><h3>Previous Speakers</h3></p>' . $pastHtml;
        }

        public function loadSpeakers() {
            // Make sure file exists
            if(!file_exists($this->dataFile)) {
                file_put_contents($this->dataFile, Yaml::dump(array()));
            }
            // Load speakers
            $this->speakers = Yaml::parse(file_get_contents($this->dataFile));
            if($this->speakers === null) $this->speakers = array();
        }

    }

    function addspeakerform($administro) {
        $params = $administro->verifyParameters('addspeaker', array('name', 'topic', 'month', 'year'));
        if($params !== false) {
            // Verify permission
            if($administro->hasPermission('admin.speakers')) {
                $administro->plugins['Speakers']->loadSpeakers();
                $speakers = $administro->plugins['Speakers']->speakers;
                $date = $params['year'] . '-' . $params['month'];
                // Make sure speaker does not exist
                if(!isset($speakers[$date])) {
                    // Save the speakers
                    $speaker = array(
                        'name' => $params['name'],
                        'topic' => $params['topic'],
                        'presentation' => false
                    );
                    $speakers[$date] = $speaker;
                    file_put_contents($administro->plugins['Speakers']->dataFile, Yaml::dump($speakers));
                    $administro->redirect('admin/speakers', 'good/Added speaker!');
                } else {
                    $administro->redirect('admin/speakers', 'bad/Speaker already exists!');
                }
            } else {
                $administro->redirect('admin/home', 'bad/You do not have permission!');
            }
        } else {
            $administro->redirect('admin/speakers', 'bad/Invalid parameters!');
        }
    }
