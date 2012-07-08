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
?><!DOCTYPE HTML>
<html lang='en'>
<head>
    <title><?php echo $title ?></title>
    <?php
        echo $this->helpers->stylesheet
            ->add($this->load_asset('css/auth.css', n("lib/controllers/components/auth/assets/css/auth.css")))
            ->add($this->load_asset("css/fx.css"))
            ->add($this->load_asset('css/forms.css', n("lib/views/helpers/forms/css/forms.css")))
            ->context('auth');

        echo $this->helpers->javascript
            ->add($this->load_asset('js/jquery.js'))
            ->context('auth');
    ?>
</head>
<body>
    <div id="header">
        <h1><?php echo $app_name ?></h1>
    </div>
    <div id="body">
        <?php echo $contents ?>
    </div>
</body>
</html>

