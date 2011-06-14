<?php
core_control_FrontController::connect(SYS_USER_LOGIN, SYS_USER_PASS, DATABASE_NAME);

$generisResourceClass = new core_kernel_classes_Class(CLASS_GENERIS_RESOURCE);


$subscptionClass = core_kernel_classes_ClassFactory::createSubClass($generisResourceClass, 'Subscription' , 'Subscribtion Class to manage distributed data',CLASS_SUBCRIPTION);
$subscriptionUrlProp = core_kernel_classes_ClassFactory::createProperty($subscptionClass,'Url', 'Distributed Generis Url of the subscription',false,PROPERTY_SUBCRIPTION_URL);
$subscriptionMaskProp = core_kernel_classes_ClassFactory::createProperty($subscptionClass,'Mask', 'Distributed Generis Mask of the subscription',false,PROPERTY_SUBCRIPTION_MASK);

$maskClass = core_kernel_classes_ClassFactory::createSubClass($generisResourceClass, 'Mask' , 'Mask Class to manage right',CLASS_MASK);
$subscriptionMaskProp->setRange($maskClass);
$maskSubjectProp = core_kernel_classes_ClassFactory::createProperty($maskClass,'Subject', 'Allowed Subject by the mask',false,PROPERTY_MASK_SUBJECT);
$maskSubjectProp->setRange(new core_kernel_classes_Class(RDF_RESOURCE));
$maskPredicateProp = core_kernel_classes_ClassFactory::createProperty($maskClass,'Predicate', 'Allowed Predicate by the mask',false,PROPERTY_MASK_PREDICATE);
$maskPredicateProp->setRange(new core_kernel_classes_Class(RDF_PROPERTY));
$maskObjectProp = core_kernel_classes_ClassFactory::createProperty($maskClass,'Object', 'Allowed Object by the mask',false,PROPERTY_MASK_PREDICATE);
$maskObjectProp->setRange(new core_kernel_classes_Class(RDF_RESOURCE));

