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
            array_push($this->administro->forms, 'addspeaker', 'deletespeaker', 'speakerpresentation');
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


            ksort($futureSpeakers);
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
                if($speaker['presentation'] !== false && $this->administro->hasPermission('member.view')) {
                    $presentation = ' [<a href="' . $this->administro->baseDir . 'speakerfile/' . $speaker['presentation'] . '">Presentation</a>]';
                }
                $futureHtml .= '<p><b>' . $month . ' ' . $year . ': </b>' . $speaker['name'] . $topic . $presentation . '</p>';
                $c++;
            }

            $c = 1;
            foreach($pastSpeakers as $date => $speaker) {
                if($c > 12) break;
                $speakerDate = new DateTime('second Friday of ' . $date);
                $year = $speakerDate->format('Y');
                $month = $speakerDate->format('F');
                $topic = '';
                if(!empty($speaker['topic'])) {
                    $topic = ' - ' . $speaker['topic'];
                }
                $presentation = '';
                if($speaker['presentation'] !== false && $this->administro->hasPermission('member.view')) {
                    $presentation = ' [<a href="' . $this->administro->baseDir . 'speakerfile/' . $speaker['presentation'] . '">Presentation</a>]';
                }
                $pastHtml .= '<p><b>' . $month . ' ' . $year . ': </b>' . $speaker['name'] . $topic . $presentation . '</p>';
                $c++;
            }

            return '<p><h3>Upcoming Speakers</h3></p>' . $futureHtml . '<p><h3>Previous Speakers</h3></p>' . $pastHtml;
        }

        public function onCleanData() {
            if(!isset($this->speakers)) $this->loadSpeakers();
            // Read speakers
            $pastSpeakers = array();
            foreach($this->speakers as $date => $speaker) {
                $speakerDate = new DateTime('second Friday of ' . $date);
                if($speakerDate < new DateTime()) {
                    $pastSpeakers[$date] = $speaker;
                }
            }

            krsort($pastSpeakers);

            $c = 1;
            foreach($pastSpeakers as $date => $speaker) {

                if($c > 12) {
                    if($speaker['presentation'] !== false) {
                        @unlink($this->presentations . $speaker['presentation']);
                    }

                    unset($this->speakers[$date]);
                }

                $c++;
            }

            file_put_contents($this->dataFile, Yaml::dump($this->speakers));
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

    function deletespeakerform($administro) {
        $params = $administro->verifyParameters('deletespeaker', array('speaker'), true, $_GET);
        if($params !== false) {
            if($administro->hasPermission('admin.speakers')) {
                $plugin = $administro->plugins['Speakers'];
                $plugin->loadSpeakers();
                // Delete presentation
                if(isset($plugin->speakers[$params['speaker']])) {
                    $speaker = $plugin->speakers[$params['speaker']];
                    if($speaker['presentation'] !== false) {
                        @unlink($plugin->presentations . $speaker['presentation']);
                    }
                }
                // Remove event
                unset($plugin->speakers[$params['speaker']]);
                file_put_contents($plugin->dataFile, Yaml::dump($plugin->speakers));
                $administro->redirect('admin/speakers', 'good/Deleted event!');
            } else {
                $administro->redirect('admin/speakers', 'bad/Invalid permission!');
            }
        } else {
            $administro->redirect('admin/speakers', 'bad/Invalid parameters!');
        }
    }

    function speakerpresentationform($administro) {
        $params = $administro->verifyParameters('speakerpresentation', array('speaker'));
        if($params !== false && isset($_FILES['file']) && !empty($_FILES['file']['name'])) {
            if($administro->hasPermission('admin.speakers')) {
                $plugin = $administro->plugins['Speakers'];
                $plugin->loadSpeakers();
                $speakerId = $params['speaker'];
                if (!isset($plugin->speakers[$speakerId])) {
                    $administro->redirect('admin/speakers', 'bad/Speaker does not exist!');
                }
                // Upload presentation
                $file = $speakerId . '.' . pathinfo($_FILES['file']['name'])['extension'];
                $plugin->speakers[$speakerId]['presentation'] = $file;
                // Save the file
                if ($_FILES['file']['size'] > 50000000) {
                    $administro->redirect('admin/speakers', 'bad/File must be under 50MB!');
                }
                move_uploaded_file($_FILES['file']['tmp_name'],
                    $plugin->presentations . $file);
                // Save event
                file_put_contents($plugin->dataFile, Yaml::dump($plugin->speakers));
                $administro->redirect('admin/speakers', 'good/Uploaded presentation!');
            } else {
                $administro->redirect('admin/speakers', 'bad/Invalid permission!');
            }
        } else {
            $administro->redirect('admin/speakers', 'bad/Invalid parameters!');
        }
    }
