# Copyright (C) 2019 andrew-ld <https://github.com/andrew-ld>
#
# This file is part of mtproto-streaming-radio.
#
# mtproto-streaming-radio is free software: you can redistribute it and/or modify
# it under the terms of the GNU Lesser General Public License as published
# by the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# mtproto-streaming-radio is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU Lesser General Public License for more details.
#
# You should have received a copy of the GNU Lesser General Public License
# along with mtproto-streaming-radio. If not, see <http://www.gnu.org/licenses/>.

import os
import os.path
import time

URL = "http://...."
OUTPUT = "/tmp/chucks/"
FILTER_FLAGS = "aresample=async=1000000[as]; [as]aresample=48000[lm]; [lm]superequalizer[final]"
CONN_FLAGS = "-reconnect 1 -reconnect_at_eof 1 -reconnect_streamed 1 -reconnect_delay_max 20"
ENC_FLAGS = "-f s16le -acodec pcm_s16le "
SEGMENT_FLAGS = "-f segment -segment_time 60"
POST_FILTER_FLAGS = "-ac 1"

FFMPEG = f"ffmpeg -i '{URL}' {CONN_FLAGS} {ENC_FLAGS} -y -filter_complex '{FILTER_FLAGS}' -map [final] {POST_FILTER_FLAGS} {SEGMENT_FLAGS} '{OUTPUT}%010d.wav'"

if os.path.isdir(OUTPUT):
 for file in os.listdir(OUTPUT):
  os.remove(OUPUT + file)

else:
 os.mkdir(OUTPUT)

os.popen(FFMPEG)

while True:
 time.sleep(30)
 files = os.listdir(OUTPUT)

 if len(files) > 4:
  os.remove(OUTPUT + min(files))
