<fieldset>
	<legend><?php echo $legend; ?></legend>
	<?php foreach ($inputs as $label => $value): ?>
	<label><?php echo $label ?><input type="text" name="<?php echo $key.'['.$label.']'; ?>" value="<?php echo $value; ?>"></label><br>
	<?php endforeach; ?>
</fieldset>