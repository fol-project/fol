<?php
include('../../../../bootstrap.php');

(new Apps\Web\App)->handleRequest(null, 'files')->send();