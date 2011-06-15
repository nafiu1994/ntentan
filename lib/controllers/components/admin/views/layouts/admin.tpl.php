<?php
/*
 * Copyright 2010 James Ekow Abaka Ainooson
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 */
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <title><?php echo $title ?></title>
    <?php
        echo $this->helpers->stylesheet
            ->add($this->load_asset('css/admin.css', $stylesheet))
            ->add($this->load_asset("css/fx.css"))
            ->add($this->load_asset('css/forms.css', n("lib/views/helpers/forms/css/forms.css")))
            ->add($this->load_asset("css/grid.css"))
            ->add($this->load_asset('css/list.css', n('lib/views/helpers/lists/css/default.css')))
            ->context('admin');
        echo $this->helpers->stylesheet($extra_stylesheet);

        echo $this->helpers->javascript
            ->add($this->load_asset('js/jquery.js'))
            ->context('admin');
        echo $this->helpers->javascript($extra_javascript);
    ?>
</head>
<body>
<div class="row" id="header">
    <div class="column grid_10_6">
        <h1><?php echo $app_name ?></h1>
        <h2>Administrator Console</h2>
    </div>
    <div class="column grid_10_4">
        <div id='profile'>Logged in as <?php echo $username ?>. <a href="<?php echo $logout_route ?>">Log out</a></div>
    </div>
</div>
<div class="row">
    <div class="column grid_20_4">
        <?php echo $this->widgets->menu($sections_menu)->alias('sections') ?></div>
    <div class="column grid_20_16">
        <div id="admin-contents">
            <?php echo $contents ?>
        </div>
    </div>
</div>
</body>
</html>
