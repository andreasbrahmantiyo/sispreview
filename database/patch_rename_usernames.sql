START TRANSACTION;

UPDATE users
SET username = 'dir_handoyo',
    name = 'Pak Handoyo',
    updated_at = NOW()
WHERE username = 'pak_handoyo';

UPDATE users
SET username = 'dir_rspad',
    name = 'Direktur RSPAD',
    updated_at = NOW()
WHERE username = 'pak_direktur';

UPDATE users
SET username = 'dev_heri',
    name = 'Pak Heri',
    updated_at = NOW()
WHERE username = 'pak_heri';

UPDATE users
SET username = 'dev_febio',
    name = 'Pak Febio',
    updated_at = NOW()
WHERE username = 'pak_febio';

UPDATE users
SET username = 'dev_morris',
    name = 'Pak Morris',
    updated_at = NOW()
WHERE username = 'pak_morris';

COMMIT;

SELECT id, username, name, role, is_active
FROM users
ORDER BY id;