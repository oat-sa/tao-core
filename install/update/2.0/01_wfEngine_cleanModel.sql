/*clean old service*/
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i118589195844398' AND `modelID`=15 LIMIT 8;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/Interview.rdf#i122051242918670' AND `modelID`=16 LIMIT 18;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i120582905838908' AND `modelID`=15 LIMIT 10;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i118596173523312' AND `modelID`=15 LIMIT 7;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i118596550348512' AND `modelID`=15 LIMIT 7;

/*Clean old process variables:  */
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i118589169135658' AND `modelID`=15 LIMIT 9;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i1190722926085852700' AND `modelID`=15 LIMIT 10;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i118589201514666' AND `modelID`=15 LIMIT 10;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i1204881889055805800' AND `modelID`=15 LIMIT 9;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/Interview.rdf#i121939168717368' AND `modelID`=16 LIMIT 18;

/*Clean remaining interviewee model*/
DELETE FROM `statements` WHERE `modelID` = 16 LIMIT 799;

/*Clean old roles: wfEngine, item creator, translator, coach, interviewer, subject*/
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i121930137765188' AND `modelID`=15 LIMIT 6;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i118596618964' AND `modelID`=15 LIMIT 6;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i118589159162296' AND `modelID`=15 LIMIT 6;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i120593713755322' AND `modelID`=15 LIMIT 6;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i119027826740428' AND `modelID`=15 LIMIT 6;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i121930698442742' AND `modelID`=15 LIMIT 6;

/*clean model*/
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i124748211635858' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i124299886328854' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i124299860710746' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i124299788641244' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i124299784247410' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i124653558221396' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i12429977703072' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i124299711659490' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i12429950482224' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i1242827641005433300' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i124326462812324' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i12445408585808' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i124705207723678' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i124705207723679' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i124705207523877' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i124705207523677' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i124454126218994' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i12525951541482' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i120706038517836' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i12193017041224' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i120593894233350' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i120593879614028' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i120593870855784' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i120593807459280' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i120593803537920' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i120593708264040' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i120593778940310' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i120593691150916' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i12059368959102' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i120593686827814' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i120593682428190' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i120593655359456' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i12059365389826' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i120593652624498' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i120593651138214' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i120593648023494' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i120593509540782' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i120593327411438' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i120593325529592' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i120593322519644' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i121930518410572' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i121334792744870' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i119020355350334' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i119012256329986' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i12133479382392' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i119012169222836' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i11894081698306' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i118597347733324' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i118597336113868' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i11859665003194' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i118596605058370' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i118596593722298' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i118596230638446' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i118596225329180' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i118596206811734' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i118595604963322' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i118595596250070' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i118595593412394' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i118595367648092' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i118595300411216' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i118595179053976' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i118588782938766' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i122346531841048' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i12193017041224' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i12133479157732' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i121334781723118' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i121334783125266' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i121334785420426' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i121334775762164' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i121334779343888' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i12133481401410' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i122182434950916' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i122286340331594' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i122346533932400' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i122346640532066' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i122347196613578' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#inferenceRule' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#inferenceRuleThen' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#inferenceRuleElse' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#activityInferenceRule' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#activityOnBeforeInferenceRule' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i123383820311354' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#exitCode' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#procActionCode' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#procExitCode' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#actionCode' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i122362252653400' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#var_processinstance' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i119012711429320' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i121930140334810' AND `modelID`=15 LIMIT 20;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/middleware/taoqual.rdf#i122182427816968' AND `modelID`=15 LIMIT 20;


/*renaming uris*/
UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#ClassProcessDefinitions" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i118588753722590";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#ClassProcessDefinitions" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i118588753722590";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#ClassProcessDefinitions" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i118588753722590";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyProcessVariables" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i118589204618246";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyProcessVariables" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i118589204618246";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyProcessVariables" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i118589204618246";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyProcessDiagramData" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#diagramData";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyProcessDiagramData" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#diagramData";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyProcessDiagramData" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#diagramData";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyProcessActivities" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i118735548956256";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyProcessActivities" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i118735548956256";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyProcessActivities" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i118735548956256";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyProcessInitRestrictedUser" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i127538500955302";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyProcessInitRestrictedUser" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i127538500955302";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyProcessInitRestrictedUser" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i127538500955302";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyProcessInitRestrictedRole" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i127538512347998";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyProcessInitRestrictedRole" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i127538512347998";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyProcessInitRestrictedRole" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i127538512347998";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyProcessInitAccesControlMode" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i127538492619476";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyProcessInitAccesControlMode" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i127538492619476";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyProcessInitAccesControlMode" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i127538492619476";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#ClassRole" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#Role";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#ClassRole" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#Role";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#ClassRole" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#Role";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#ClassProcessInstances" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i119010455660544";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#ClassProcessInstances" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i119010455660544";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#ClassProcessInstances" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i119010455660544";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyProcessInstancesStatus" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i119011843917578";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyProcessInstancesStatus" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i119011843917578";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyProcessInstancesStatus" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i119011843917578";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyProcessInstancesExecutionOf" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i119010459643422";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyProcessInstancesExecutionOf" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i119010459643422";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyProcessInstancesExecutionOf" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i119010459643422";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyProcessInstancesCurrentToken" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i119011853150574";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyProcessInstancesCurrentToken" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i119011853150574";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyProcessInstancesCurrentToken" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i119011853150574";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyProcessInstancesProcessPath" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#processPath";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyProcessInstancesProcessPath" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#processPath";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyProcessInstancesProcessPath" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#processPath";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyProcessInstancesProcessFullPath" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#processFullPath";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyProcessInstancesProcessFullPath" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#processFullPath";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyProcessInstancesProcessFullPath" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#processFullPath";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#InstanceStatusResumed" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i119011839432700";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#InstanceStatusResumed" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i119011839432700";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#InstanceStatusResumed" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i119011839432700";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#InstanceStatusStarted" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i119011838314196";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#InstanceStatusStarted" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i119011838314196";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#InstanceStatusStarted" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i119011838314196";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#InstanceStatusFinished" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i119011840560280";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#InstanceStatusFinished" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i119011840560280";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#InstanceStatusFinished" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i119011840560280";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#InstanceStatusPaused" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i119011862341174";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#InstanceStatusPaused" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i119011862341174";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#InstanceStatusPaused" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i119011862341174";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#ClassProcessVariables" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i118589004639950";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#ClassProcessVariables" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i118589004639950";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#ClassProcessVariables" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i118589004639950";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyCode" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#code";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyCode" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#code";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyCode" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#code";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#ClassActivities" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i118588757437650";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#ClassActivities" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i118588757437650";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#ClassActivities" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i118588757437650";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivitiesInteractiveServices" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i118588789618848";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivitiesInteractiveServices" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i118588789618848";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivitiesInteractiveServices" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i118588789618848";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyAllowFreeValueOf" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i122354397139712";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyAllowFreeValueOf" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i122354397139712";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyAllowFreeValueOf" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i122354397139712";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivitiesRestrictedUser" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i127488521314416";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivitiesRestrictedUser" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i127488521314416";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivitiesRestrictedUser" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i127488521314416";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivitiesRestrictedRole" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i127488515463210";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivitiesRestrictedRole" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i127488515463210";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivitiesRestrictedRole" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i127488515463210";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivitiesAccessControlMode" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i127488549329046";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivitiesAccessControlMode" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i127488549329046";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivitiesAccessControlMode" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i127488549329046";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivitiesHidden" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#isHiddenActivity";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivitiesHidden" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#isHiddenActivity";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivitiesHidden" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#isHiddenActivity";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivitiesInitial" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i119018447833116";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivitiesInitial" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i119018447833116";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivitiesInitial" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i119018447833116";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivitiesControls" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#ActivityControl";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivitiesControls" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#ActivityControl";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivitiesControls" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#ActivityControl";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#ClassControls" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#Controls";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#ClassControls" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#Controls";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#ClassControls" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#Controls";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#InstanceControlsBackward" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#BackwardControl";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#InstanceControlsBackward" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#BackwardControl";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#InstanceControlsBackward" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#BackwardControl";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#InstanceControlsForward" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#ForwardControl";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#InstanceControlsForward" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#ForwardControl";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#InstanceControlsForward" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#ForwardControl";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#ClassConnectors" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i118589215756172";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#ClassConnectors" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i118589215756172";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#ClassConnectors" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i118589215756172";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyConnectorsTransitionRule" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i122207114241798";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyConnectorsTransitionRule" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i122207114241798";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyConnectorsTransitionRule" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i122207114241798";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyConnectorsPreviousActivities" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i118589245545368";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyConnectorsPreviousActivities" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i118589245545368";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyConnectorsPreviousActivities" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i118589245545368";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyConnectorsNextActivities" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i118589252058280";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyConnectorsNextActivities" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i118589252058280";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyConnectorsNextActivities" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i118589252058280";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyConnectorsActivityReference" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#activityReference";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyConnectorsActivityReference" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#activityReference";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyConnectorsActivityReference" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#activityReference";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyConnectorsType" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i118595164231830";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyConnectorsType" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i118595164231830";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyConnectorsType" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i118595164231830";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyConnectorsNotificationModes" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#Notify";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyConnectorsNotificationModes" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#Notify";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyConnectorsNotificationModes" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#Notify";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyConnectorsNotifiedRole" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#GroupNotified";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyConnectorsNotifiedRole" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#GroupNotified";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyConnectorsNotifiedRole" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#GroupNotified";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyConnectorsNotificationMessage" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#NotificationMessage";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyConnectorsNotificationMessage" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#NotificationMessage";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyConnectorsNotificationMessage" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#NotificationMessage";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#ClassTypeOfConnectors" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i118589088163970";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#ClassTypeOfConnectors" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i118589088163970";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#ClassTypeOfConnectors" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i118589088163970";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#InstanceTypeOfConnectorsConditional" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i118589220353990";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#InstanceTypeOfConnectorsConditional" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i118589220353990";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#InstanceTypeOfConnectorsConditional" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i118589220353990";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#InstanceTypeOfConnectorsSequence" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i118589243226718";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#InstanceTypeOfConnectorsSequence" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i118589243226718";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#InstanceTypeOfConnectorsSequence" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i118589243226718";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#InstanceTypeOfConnectorsParallel" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#connectorParallel";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#InstanceTypeOfConnectorsParallel" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#connectorParallel";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#InstanceTypeOfConnectorsParallel" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#connectorParallel";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#InstanceTypeOfConnectorsJoin" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#connectorJoin";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#InstanceTypeOfConnectorsJoin" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#connectorJoin";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#InstanceTypeOfConnectorsJoin" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#connectorJoin";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#ClassTransitionRules" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i122206969324866";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#ClassTransitionRules" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i122206969324866";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#ClassTransitionRules" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i122206969324866";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyTransitionRulesThen" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i122207070428322";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyTransitionRulesThen" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i122207070428322";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyTransitionRulesThen" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i122207070428322";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyTransitionRulesElse" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i122207096147834";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyTransitionRulesElse" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i122207096147834";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyTransitionRulesElse" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i122207096147834";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#ClassNotificationMode" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#NotificationMode";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#ClassNotificationMode" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#NotificationMode";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#ClassNotificationMode" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#NotificationMode";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#InstanceNotifyUser" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#NotifyUser";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#InstanceNotifyUser" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#NotifyUser";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#InstanceNotifyUser" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#NotifyUser";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#InstanceNotifyNextActivityUsers" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#NotifyNextActivityUsers";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#InstanceNotifyNextActivityUsers" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#NotifyNextActivityUsers";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#InstanceNotifyNextActivityUsers" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#NotifyNextActivityUsers";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#InstanceNotifyPreviousActivityUsers" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#NotifyPreviousActivityUsers";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#InstanceNotifyPreviousActivityUsers" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#NotifyPreviousActivityUsers";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#InstanceNotifyPreviousActivityUsers" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#NotifyPreviousActivityUsers";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#InstanceNotifyRole" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#NotifyGroup";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#InstanceNotifyRole" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#NotifyGroup";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#InstanceNotifyRole" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#NotifyGroup";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#ClassNotification" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#Notification";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#ClassNotification" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#Notification";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#ClassNotification" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#Notification";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyNotificationTo" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#NotificationTo";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyNotificationTo" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#NotificationTo";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyNotificationTo" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#NotificationTo";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyNotificationConnector" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#NotificationConnector";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyNotificationConnector" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#NotificationConnector";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyNotificationConnector" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#NotificationConnector";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyNotificationProcessExecution" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#NotificationProcessExecution";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyNotificationProcessExecution" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#NotificationProcessExecution";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyNotificationProcessExecution" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#NotificationProcessExecution";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyNotificationSent" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#NotificationSent";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyNotificationSent" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#NotificationSent";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyNotificationSent" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#NotificationSent";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyNotificationDate" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#NotificationDate";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyNotificationDate" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#NotificationDate";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyNotificationDate" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#NotificationDate";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#ClassCallOfServices" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i118595077025536";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#ClassCallOfServices" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i118595077025536";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#ClassCallOfServices" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i118595077025536";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyCallOfServicesServiceDefinition" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i11859509039346";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyCallOfServicesServiceDefinition" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i11859509039346";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyCallOfServicesServiceDefinition" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i11859509039346";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyCallOfServicesActualParameterOut" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i118596586150000";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyCallOfServicesActualParameterOut" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i118596586150000";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyCallOfServicesActualParameterOut" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i118596586150000";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyCallOfServicesActualParameterin" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i118595099928140";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyCallOfServicesActualParameterin" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i118595099928140";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyCallOfServicesActualParameterin" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i118595099928140";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyCallOfServicesTop" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#serviceStyleTop";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyCallOfServicesTop" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#serviceStyleTop";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyCallOfServicesTop" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#serviceStyleTop";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyCallOfServicesLeft" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#serviceStyleLeft";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyCallOfServicesLeft" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#serviceStyleLeft";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyCallOfServicesLeft" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#serviceStyleLeft";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyCallOfServicesWidth" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#serviceStyleWidth";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyCallOfServicesWidth" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#serviceStyleWidth";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyCallOfServicesWidth" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#serviceStyleWidth";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyCallOfServicesHeight" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#serviceStyleHeight";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyCallOfServicesHeight" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#serviceStyleHeight";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyCallOfServicesHeight" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#serviceStyleHeight";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#ClassServiceDefinitions" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i118588759532084";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#ClassServiceDefinitions" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i118588759532084";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#ClassServiceDefinitions" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i118588759532084";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyServiceDefinitionsFormalParameterOut" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i118588897651172";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyServiceDefinitionsFormalParameterOut" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i118588897651172";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyServiceDefinitionsFormalParameterOut" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i118588897651172";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyServiceDefinitionsFormalParameterIn" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i118588892919658";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyServiceDefinitionsFormalParameterIn" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i118588892919658";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyServiceDefinitionsFormalParameterIn" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i118588892919658";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#ClassSupportServices" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i118588779325312";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#ClassSupportServices" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i118588779325312";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#ClassSupportServices" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i118588779325312";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertySupportServicesUrl" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i11858886911216";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertySupportServicesUrl" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i11858886911216";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertySupportServicesUrl" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i11858886911216";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#ClassWebServices" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i118588763446870";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#ClassWebServices" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i118588763446870";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#ClassWebServices" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i118588763446870";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#ClassFormalParameters" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i118588904546812";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#ClassFormalParameters" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i118588904546812";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#ClassFormalParameters" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i118588904546812";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyFormalParametersDefaultConstantValue" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i118588964565322";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyFormalParametersDefaultConstantValue" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i118588964565322";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyFormalParametersDefaultConstantValue" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i118588964565322";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyFormalParametersDefaultProcessVariable" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i118588964565323";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyFormalParametersDefaultProcessVariable" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i118588964565323";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyFormalParametersDefaultProcessVariable" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i118588964565323";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyFormalParametersName" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i118588911964016";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyFormalParametersName" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i118588911964016";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyFormalParametersName" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i118588911964016";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#ClassActualParameters" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i118588960462136";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#ClassActualParameters" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i118588960462136";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#ClassActualParameters" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i118588960462136";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActualParametersProcessVariable" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i11858901499008";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActualParametersProcessVariable" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i11858901499008";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActualParametersProcessVariable" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i11858901499008";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActualParametersQualityMetric" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i118589023027962";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActualParametersQualityMetric" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i118589023027962";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActualParametersQualityMetric" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i118589023027962";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActualParametersConstantValue" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i1185890127346";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActualParametersConstantValue" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i1185890127346";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActualParametersConstantValue" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i1185890127346";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActualParametersFormalParameter" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i118588973457282";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActualParametersFormalParameter" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i118588973457282";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActualParametersFormalParameter" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i118588973457282";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#ClassActivityExecutions" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i127488468464144";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#ClassActivityExecutions" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i127488468464144";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#ClassActivityExecutions" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i127488468464144";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivityExecutionsExecutionOf" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i127488474048090";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivityExecutionsExecutionOf" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i127488474048090";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivityExecutionsExecutionOf" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i127488474048090";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivityExecutionsCurrentUser" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i127488502051180";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivityExecutionsCurrentUser" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i127488502051180";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivityExecutionsCurrentUser" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i127488502051180";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivityExecutionsFinished" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i127488483924334";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivityExecutionsFinished" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i127488483924334";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivityExecutionsFinished" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i127488483924334";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivityExecutionsProcessExecution" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i127488502051181";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivityExecutionsProcessExecution" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i127488502051181";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivityExecutionsProcessExecution" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i127488502051181";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivityExecutionsContextRecovery" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#ContextRecovery";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivityExecutionsContextRecovery" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#ContextRecovery";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivityExecutionsContextRecovery" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#ContextRecovery";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#ClassAccessControlModes" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i127488525924294";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#ClassAccessControlModes" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i127488525924294";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#ClassAccessControlModes" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i127488525924294";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyAccessControlModesRole" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i127488532261318";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyAccessControlModesRole" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i127488532261318";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyAccessControlModesRole" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i127488532261318";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyAccessControlModesRoleRestrictedUser" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i12748853466806";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyAccessControlModesRoleRestrictedUser" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i12748853466806";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyAccessControlModesRoleRestrictedUser" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i12748853466806";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyAccessControlModesUser" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i12748853753356";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyAccessControlModesUser" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i12748853753356";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyAccessControlModesUser" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i12748853753356";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyAccessControlModesRoleRestrictedUserInherited" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i12748853932510";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyAccessControlModesRoleRestrictedUserInherited" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i12748853932510";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyAccessControlModesRoleRestrictedUserInherited" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i12748853932510";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#ClassTokens" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#Token";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#ClassTokens" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#Token";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#ClassTokens" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#Token";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyTokensVariable" WHERE `subject` = "http://www.tao.lu/middleware/taoqual.rdf#i127565965852576";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyTokensVariable" WHERE `predicate` = "http://www.tao.lu/middleware/taoqual.rdf#i127565965852576";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyTokensVariable" WHERE `object` = "http://www.tao.lu/middleware/taoqual.rdf#i127565965852576";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyTokensActivity" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#TokenActivity";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyTokensActivity" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#TokenActivity";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyTokensActivity" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#TokenActivity";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyTokensActivityExecution" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i127565960419850";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyTokensActivityExecution" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i127565960419850";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyTokensActivityExecution" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i127565960419850";



UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#ClassCallOfservicesResources" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i119969519057014";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#ClassCallOfservicesResources" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i119969519057014";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#ClassCallOfservicesResources" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i119969519057014";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#ClassServiceDefinitionResources" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i119969508736164";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#ClassServiceDefinitionResources" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i119969508736164";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#ClassServiceDefinitionResources" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i119969508736164";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#ClassProcessDefinitionResources" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i119969482458020";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#ClassProcessDefinitionResources" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i119969482458020";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#ClassProcessDefinitionResources" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i119969482458020";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#ClassServicesResources" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i119969468033908";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#ClassServicesResources" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i119969468033908";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#ClassServicesResources" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i119969468033908";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#ClassProcessExecutionResources" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i119969454940384";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#ClassProcessExecutionResources" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i119969454940384";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#ClassProcessExecutionResources" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i119969454940384";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#ClassProcessInstanceStatus" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i119011835256954";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#ClassProcessInstanceStatus" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i119011835256954";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#ClassProcessInstanceStatus" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i119011835256954";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyFormalParameterDataType" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i118588913861178";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyFormalParameterDataType" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i118588913861178";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyFormalParameterDataType" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i118588913861178";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyWebServicesWsdlUrl" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i118588854260310";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyWebServicesWsdlUrl" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i118588854260310";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyWebServicesWsdlUrl" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i118588854260310";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyFormalParameterName" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#i118588810947052";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyFormalParameterName" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#i118588810947052";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyFormalParameterName" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#i118588810947052";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyConnectorsNotifiedUser" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#UserNotified";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyConnectorsNotifiedUser" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#UserNotified";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyConnectorsNotifiedUser" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#UserNotified";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#ClassActivityControls" WHERE subject = "http://www.tao.lu/middleware/taoqual.rdf#ActivityControl";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#ClassActivityControls" WHERE predicate = "http://www.tao.lu/middleware/taoqual.rdf#ActivityControl";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#ClassActivityControls" WHERE object = "http://www.tao.lu/middleware/taoqual.rdf#ActivityControl";

/*taoqual-localhost namespace!!!*/
UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyTokensCurrentUser" WHERE subject = "http://localhost/middleware/taoqual.rdf#i127565939760310";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyTokensCurrentUser" WHERE predicate = "http://localhost/middleware/taoqual.rdf#i127565939760310";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyTokensCurrentUser" WHERE object = "http://localhost/middleware/taoqual.rdf#i127565939760310";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyTokensVariable" WHERE subject = "http://localhost/middleware/taoqual.rdf#i127565965852576";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyTokensVariable" WHERE predicate = "http://localhost/middleware/taoqual.rdf#i127565965852576";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyTokensVariable" WHERE object = "http://localhost/middleware/taoqual.rdf#i127565965852576";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivityExecution" WHERE subject = "http://localhost/middleware/taoqual.rdf#i127565960419850";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivityExecution" WHERE predicate = "http://localhost/middleware/taoqual.rdf#i127565960419850";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivityExecution" WHERE object = "http://localhost/middleware/taoqual.rdf#i127565960419850";

UPDATE statements SET subject = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivity" WHERE subject = "http://localhost/middleware/taoqual.rdf#TokenActivity";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivity" WHERE predicate = "http://localhost/middleware/taoqual.rdf#TokenActivity";
UPDATE statements SET object = "http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivity" WHERE object = "http://localhost/middleware/taoqual.rdf#TokenActivity";


