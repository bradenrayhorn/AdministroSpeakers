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

            $futureHtml = '';
            $pastHtml = '';

            foreach($futureSpeakers as $date => $speaker) {
                $speakerDate = new DateTime('second Friday of ' . $date);
                $year = $speakerDate->format('Y');
                $month = $speakerDate->format('F');
                $futureHtml .= '<p><b>' . $month . ' ' . $year . ': </b>' . $speaker['name'] . ' - ' . $speaker['topic'] . '</p>';
            }

            foreach($pastSpeakers as $date => $speaker) {
                $speakerDate = new DateTime('second Friday of ' . $date);
                $year = $speakerDate->format('Y');
                $month = $speakerDate->format('F');
                $pastHtml .= '<p><b>' . $month . ' ' . $year . ': </b>' . $speaker['name'] . ' - ' . $speaker['topic'] . '</p>';
            }

            return '<p><h2>Upcoming Speakers</h2></p>' . $futureHtml . '<p><h2>Previous Speakers</h2></p>' . $pastHtml;
        }

        public function loadSpeakers() {
            // Make sure file exists
            if(!file_exists($this->dataFile)) {
                file_put_contents($this->dataFile, Yaml::dump(array()));
            }
            // Load speakers
            $this->speakers = Yaml::parse(file_get_contents($this->dataFile));
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
