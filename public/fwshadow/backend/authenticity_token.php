<?php
echo sha1(md5(date("r") . rand(1, 100000000) % 1000003));