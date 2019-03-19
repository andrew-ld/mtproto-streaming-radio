<?php

/*
Copyright (C) 2019 andrew-ld <https://github.com/andrew-ld>

This file is part of mtproto-streaming-radio.

mtproto-streaming-radio is free software: you can redistribute it and/or modify
it under the terms of the GNU Lesser General Public License as published
by the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

mtproto-streaming-radio is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public License
along with mtproto-streaming-radio. If not, see <http://www.gnu.org/licenses/>.
*/

/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUndefinedMethodInspection */

include 'madeline.php';

$MadelineProto = new \danog\MadelineProto\API('session.madeline');
$MadelineProto->start();


class EventHandler extends \danog\MadelineProto\EventHandler {
    protected $calls = [];
    protected $chucks = "/tmp/chucks/";

    function configureCall($call) {
        $call->configuration['enable_NS'] = false;
        $call->configuration['enable_AGC'] = false;
        $call->configuration['enable_AEC'] = false;
        $call->configuration['shared_config']['audio_min_bitrate'] = 110 * 1000;
        $call->configuration['shared_config']['audio_max_bitrate'] = 110 * 1000;
        $call->configuration['shared_config']['audio_init_bitrate'] = 110 * 1000;
        $call->configuration['shared_config']['audio_congestion_window'] = 4 * 1024;
        $call->parseConfig();
    }

    function getLastChuck() {
        return $this->chucks . scandir($this->chucks, SCANDIR_SORT_DESCENDING)[0];
    }

    public function onUpdatePhoneCall($update) {
        if (is_object($update['phone_call'])) {
            $state = $update['phone_call']->getCallState();

            if ($state == \danog\MadelineProto\VoIP::CALL_STATE_INCOMING) {
                $this->configureCall($update['phone_call']);
                $call = $update['phone_call']->accept();

                if ($call !== false) {
                    $chk = $this->getLastChuck();
                    $this->calls[] = [$call, $chk];

                    $call->play($chk);
                }
            }
        }
    }

    public function onLoop() {
        $chk = $this->getLastChuck();

        foreach ($this->calls as $call_id => $call) {
            $state = $call[0]->getCallState();

            if ($state < \danog\MadelineProto\VoIP::CALL_STATE_ENDED) {
                if (!$call[0]->isPlaying() && $call[1] != $chk) {
                    $call[0]->play($chk);
                }
            }

            if ($state == \danog\MadelineProto\VoIP::CALL_STATE_ENDED) {
                unset($this->calls[$call_id]);
            }
        }
    }
}


$MadelineProto->setEventHandler('\EventHandler');
$MadelineProto->loop();
