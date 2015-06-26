<?php
//.env file
if (!is_file('.env')) {
	copy('.env.example', '.env');
}

//logs folder
if (!is_dir('data/log/')) {
	mkdir('data/log/', 0777, true);
}
