-- Hyperclass
DELETE FROM statements WHERE modelID=18;
DELETE FROM statements WHERE subject like "http://www.tao.lu/middleware/hyperclass.rdf#%" or predicate like "http://www.tao.lu/middleware/hyperclass.rdf#%" or object like "http://www.tao.lu/middleware/hyperclass.rdf#%" ;
DELETE FROM models WHERE modelID=18;

-- Rules
DELETE FROM statements WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i12217501804784" OR predicate = "http://www.tao.lu/middleware/Rules.rdf#i12217501804784" OR object = "http://www.tao.lu/middleware/Rules.rdf#i12217501804784";
DELETE FROM statements WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i1221149293045441400" OR predicate = "http://www.tao.lu/middleware/Rules.rdf#i1221149293045441400" OR object = "http://www.tao.lu/middleware/Rules.rdf#i1221149293045441400";
DELETE FROM statements WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i1220882570492" OR predicate = "http://www.tao.lu/middleware/Rules.rdf#i1220882570492" OR object = "http://www.tao.lu/middleware/Rules.rdf#i1220882570492";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#True" WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i12339287061376" AND predicate = "http://www.w3.org/1999/02/22-rdf-syntax-ns#type" AND object ="http://www.tao.lu/middleware/Rules.rdf#i121923619228250" LIMIT 1;
UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#False" WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i12339287061376" AND predicate = "http://www.w3.org/1999/02/22-rdf-syntax-ns#type" AND object ="http://www.tao.lu/middleware/Rules.rdf#i121923619228250" LIMIT 1;

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#True" WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i12339287061376" AND predicate = "http://www.tao.lu/middleware/Rules.rdf#i121923679040932" AND object = "http://www.tao.lu/middleware/Rules.rdf#i122044076930844" LIMIT 1; 
UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#False" WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i12339287061376" AND predicate = "http://www.tao.lu/middleware/Rules.rdf#i121923679040932" AND object = "http://www.tao.lu/middleware/Rules.rdf#i122044076930844" LIMIT 1;

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#True" WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i12339287061376" AND predicate = "http://www.tao.lu/middleware/Rules.rdf#i12192453988688" AND object = "http://www.tao.lu/middleware/Rules.rdf#i121924550637344" LIMIT 1;
UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#False" WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i12339287061376" AND predicate = "http://www.tao.lu/middleware/Rules.rdf#i12192453988688" AND object = "http://www.tao.lu/middleware/Rules.rdf#i121924550637344" LIMIT 1; 

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#True" WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i12339287061376" AND predicate = "http://www.tao.lu/middleware/Rules.rdf#i121933027526502" AND object = "" LIMIT 1; 
UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#False" WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i12339287061376" AND predicate = "http://www.tao.lu/middleware/Rules.rdf#i121933027526502" AND object = "" LIMIT 1; 

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#True" WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i12339287061376" AND predicate = "http://www.tao.lu/middleware/Rules.rdf#i121983883748358" AND object = "http://www.tao.lu/middleware/Rules.rdf#i12339287061376" LIMIT 1;
UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#False" WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i12339287061376" AND predicate = "http://www.tao.lu/middleware/Rules.rdf#i121983883748358" AND object = "http://www.tao.lu/middleware/Rules.rdf#i12339287061376" LIMIT 1;

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#True" WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i12339287061376" AND predicate = "http://www.tao.lu/middleware/Rules.rdf#i121983887063930" AND object = "http://www.tao.lu/middleware/Rules.rdf#i12339287061376"  LIMIT 1;
UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#False" WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i12339287061376" AND predicate = "http://www.tao.lu/middleware/Rules.rdf#i121983887063930" AND object = "http://www.tao.lu/middleware/Rules.rdf#i12339287061376"  LIMIT 1;

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#True" WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i12339287061376" AND predicate = "http://www.w3.org/1999/02/22-rdf-syntax-ns#value" AND object = "" LIMIT 1;
UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#False" WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i12339287061376" AND predicate = "http://www.w3.org/1999/02/22-rdf-syntax-ns#value" AND object = "" LIMIT 1;

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#True" WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i12339287061376" AND predicate = "http://www.w3.org/2000/01/rdf-schema#seeAlso" AND object = "" LIMIT 1;
UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#False" WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i12339287061376" AND predicate = "http://www.w3.org/2000/01/rdf-schema#seeAlso" AND object = "" LIMIT 1;

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#True" WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i12339287061376" AND predicate = "http://www.w3.org/2000/01/rdf-schema#isDefinedBy" AND object = "" LIMIT 1;
UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#False" WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i12339287061376" AND predicate = "http://www.w3.org/2000/01/rdf-schema#isDefinedBy" AND object = "" LIMIT 1;

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#True" WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i12339287061376" AND predicate = "http://www.w3.org/2000/01/rdf-schema#comment" AND object = "Expression that is always True"  LIMIT 1;
UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#True" WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i12339287061376" AND predicate = "http://www.w3.org/2000/01/rdf-schema#label" AND object = "True"  LIMIT 1;

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#False" WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i12339287061376" AND predicate = "http://www.w3.org/2000/01/rdf-schema#comment" AND object = "Expression that is always False"  LIMIT 1;
UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#False" WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i12339287061376" AND predicate = "http://www.w3.org/2000/01/rdf-schema#label" AND object = "False"  LIMIT 1;

UPDATE statements SET object ="http://www.tao.lu/middleware/Rules.rdf#True"  WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i12339287061376";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#TermValue" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i122053861624260";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#TermValue" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i122053861624260";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#TermValue" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i122053861624260";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#Empty" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i122044076930844";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#Empty" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i122044076930844";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#Empty" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i122044076930844";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#SecondOperand" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i121983968860392";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#SecondOperand" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i121983968860392";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#SecondOperand" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i121983968860392";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#SecondExpression" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i121983887063930";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#SecondExpression" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i121983887063930";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#SecondExpression" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i121983887063930";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#FirstExpression" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i121983883748358";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#FirstExpression" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i121983883748358";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#FirstExpression" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i121983883748358";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#LessThan" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i121967417232170";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#LessThan" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i121967417232170";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#LessThan" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i121967417232170";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#LessThanOrEqual" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i121967414425426";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#LessThanOrEqual" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i121967414425426";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#LessThanOrEqual" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i121967414425426";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#GreaterThanOrEqual" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i121967412049720";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#GreaterThanOrEqual" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i121967412049720";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#GreaterThanOrEqual" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i121967412049720";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#GreaterThan" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i121967409530514";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#GreaterThan" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i121967409530514";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#GreaterThan" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i121967409530514";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#NotEqual" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i121967401043392";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#NotEqual" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i121967401043392";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#NotEqual" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i121967401043392";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#Equal" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i121967397358362";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#Equal" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i121967397358362";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#Equal" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i121967397358362";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#Iterator" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i121966713834336";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#Iterator" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i121966713834336";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#Iterator" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i121966713834336";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#Multiply" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i121966709529770";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#Multiply" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i121966709529770";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#Multiply" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i121966709529770";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#Division" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i121966708341280";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#Division" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i121966708341280";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#Division" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i121966708341280";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#Minus" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i12196670753200";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#Minus" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i12196670753200";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#Minus" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i12196670753200";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#Plus" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i121966705538672";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#Plus" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i121966705538672";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#Plus" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i121966705538672";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#ArithmeticOperator" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i121966663218078";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#ArithmeticOperator" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i121966663218078";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#ArithmeticOperator" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i121966663218078";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#FirstOperand" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i121966656313246";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#FirstOperand" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i121966656313246";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#FirstOperand" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i121966656313246";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#HasOperator" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i121966653752048";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#HasOperator" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i121966653752048";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#HasOperator" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i121966653752048";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#Operation" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i121966649362564";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#Operation" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i121966649362564";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#Operation" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i121966649362564";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#Predicate" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i121966642861026";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#Predicate" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i121966642861026";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#Predicate" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i121966642861026";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#Subject" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i121966636932662";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#Subject" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i121966636932662";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#Subject" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i121966636932662";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#Const" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i121966631826316";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#Const" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i121966631826316";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#Const" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i121966631826316";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#SubjectPredicateX" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i122174511025122";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#SubjectPredicateX" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i122174511025122";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#SubjectPredicateX" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i122174511025122";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#Debug" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i121933027526502";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#Debug" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i121933027526502";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#Debug" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i121933027526502";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#And" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i121924555054764";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#And" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i121924555054764";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#And" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i121924555054764";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#Or" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i12192455333208";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#Or" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i12192455333208";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#Or" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i12192455333208";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#Exists" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i121924550637344";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#Exists" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i121924550637344";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#Exists" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i121924550637344";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#LogicalOperator" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i121924548064116";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#LogicalOperator" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i121924548064116";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#LogicalOperator" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i121924548064116";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#asLogicalOperator" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i12192453988688";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#asLogicalOperator" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i12192453988688";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#asLogicalOperator" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i12192453988688";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#Term" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i121923706015022";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#Term" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i121923706015022";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#Term" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i121923706015022";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#TerminalExpression" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i121923679040932";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#TerminalExpression" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i121923679040932";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#TerminalExpression" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i121923679040932";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#If" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i121923621333644";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#If" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i121923621333644";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#If" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i121923621333644";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#Expression" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i121923619228250";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#Expression" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i121923619228250";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#Expression" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i121923619228250";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#Rule" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i121923538763258";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#Rule" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i121923538763258";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#Rule" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i121923538763258";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#XPredicateObject" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i122122644123850";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#XPredicateObject" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i122122644123850";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#XPredicateObject" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i122122644123850";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#Predicate" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i122122686635234";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#Predicate" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i122122686635234";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#Predicate" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i122122686635234";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#Object" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i1221227023880";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#Object" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i1221227023880";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#Object" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i1221227023880";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#Quantifier" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i122174186664646";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#Quantifier" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i122174186664646";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#Quantifier" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i122174186664646";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#ItExistsUniqueItem" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i122174197722302";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#ItExistsUniqueItem" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i122174197722302";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#ItExistsUniqueItem" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i122174197722302";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#ForAll" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i12217424403400";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#ForAll" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i12217424403400";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#ForAll" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i12217424403400";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#Set" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i122174479849952";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#Set" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i122174479849952";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#Set" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i122174479849952";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#TrippleMask" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i122174487720136";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#TrippleMask" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i122174487720136";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#TrippleMask" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i122174487720136";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#Subject" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i122174527212914";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#Subject" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i122174527212914";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#Subject" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i122174527212914";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#Predicate" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i122174533143042";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#Predicate" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i122174533143042";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#Predicate" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i122174533143042";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#ConstrcuctedSet" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i122174806846118";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#ConstrcuctedSet" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i122174806846118";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#ConstrcuctedSet" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i122174806846118";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#SetOperator" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i122174810828864";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#SetOperator" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i122174810828864";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#SetOperator" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i122174810828864";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#Union" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i122174815616954";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#Union" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i122174815616954";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#Union" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i122174815616954";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#Intersect" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i122174818041556";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#Intersect" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i122174818041556";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#Intersect" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i122174818041556";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#HasSetOperator" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i122174809757840";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#HasSetOperator" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i122174809757840";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#HasSetOperator" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i122174809757840";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#SubSets" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i122174899919264";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#SubSets" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i122174899919264";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#SubSets" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i122174899919264";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#Assignment" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i123237201537938";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#Assignment" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i123237201537938";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#Assignment" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i123237201537938";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#Variable" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i123237213836954";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#Variable" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i123237213836954";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#Variable" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i123237213836954";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#Value" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i123237307447872";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#Value" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i123237307447872";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#Value" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i123237307447872";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#IsNull" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i12331502381568";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#IsNull" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i12331502381568";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#IsNull" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i12331502381568";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#Concat" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i12335752518860";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#Concat" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i12335752518860";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#Concat" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i12335752518860";

UPDATE statements SET subject = "http://www.tao.lu/middleware/Rules.rdf#DynamicText" 	WHERE subject = "http://www.tao.lu/middleware/Rules.rdf#i122113325919616";
UPDATE statements SET predicate = "http://www.tao.lu/middleware/Rules.rdf#DynamicText" 	WHERE predicate = "http://www.tao.lu/middleware/Rules.rdf#i122113325919616";
UPDATE statements SET object = "http://www.tao.lu/middleware/Rules.rdf#DynamicText" 	WHERE object = "http://www.tao.lu/middleware/Rules.rdf#i122113325919616";

-- Widgets
DELETE FROM statements WHERE subject="http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ExpandFormButton";
DELETE FROM statements WHERE subject="http://www.tao.lu/datatypes/WidgetDefinitions.rdf#seq";
DELETE FROM statements WHERE subject="http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ListBox";

UPDATE statements SET object = "Is language dependent?" WHERE subject = "http://www.tao.lu/Ontologies/generis.rdf#is_language_dependent" AND predicate like "%label" limit 1;
