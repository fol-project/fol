<?php
//.env file
if (!is_file('.env')) {
	copy('.env.example', '.env');
}

//data folder
if (!is_dir('data')) {
	mkdir('data', 0777);
}

//logs folder
if (!is_dir('data/log')) {
	mkdir('data/log', 0777);
}
