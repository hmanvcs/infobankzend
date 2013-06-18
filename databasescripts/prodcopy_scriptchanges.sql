-- Reset all email addresses to development addresses and the password to the word password
UPDATE useraccount set email =  INSERT(email,LOCATE('@', email)+1, 22,'veritracker.com'), password = sha1('password');
UPDATE company set email =  INSERT(email,LOCATE('@', email)+1, 22,'veritracker.com');

UPDATE appconfig set optionvalue = 'admin@domain.com' WHERE optionname = 'emailmessagesender';