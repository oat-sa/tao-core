<?php
/*
 * This post-installation script creates a new local file source for file uploaded
 * by end-users through the TAO GUI.
 */
$extension = common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
$dataPath = $extension ->getConstant('BASE_PATH'). 'data' . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR;

$source = tao_models_classes_FileSourceService::singleton()->addLocalSource('fileUploadDirectory', $dataPath);
tao_models_classes_TaoService::singleton()->setDefaultUploadSource($source);