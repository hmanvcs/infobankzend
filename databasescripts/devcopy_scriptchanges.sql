 -- Reset all email addresses to development addresses and the password to the word password
 
UPDATE useraccount set email =  INSERT(email,LOCATE('@', email)+1, 22,'devmail.infomacorp.com'), password = sha1('password') where email <> '' AND isactive = 1;
UPDATE farmer set email =  INSERT(email,LOCATE('@', email)+1, 22,'devmail.infomacorp.com') where email <> '';
UPDATE farmgroup set email =  INSERT(email,LOCATE('@', email)+1, 22,'devmail.infomacorp.com') where email <> '';

 -- Overwrite admin email addresses to administrator
UPDATE useraccount set email = 'admin@devmail.infomacorp.com' WHERE id = 1;
UPDATE appconfig set optionvalue = 'admin@devmail.infomacorp.com' WHERE optionname = 'emailmessagesender' OR optionname = 'supportemailaddress';

