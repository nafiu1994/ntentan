<h<?php echo $heading_level ?>>Edit <?php echo $entity ?></h<?php echo $heading_level?>>
<?php
echo $console_menu_block;
echo $this->helpers->form->open();
$this->helpers->form->setErrors($errors);
$this->helpers->form->setData($data);
echo t("edit_{$entity}_form.tpl.php", array('fields' => $fields, 'data' => $data));
echo $this->helpers->form->close('Update');