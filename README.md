# ActiveCollab Baseline

## Checking Code Quality

### Checks

Checks are grouped into two groups:

1. Fixers - they check and modify the code. Modified files are automatically staged,
2. Analyzers - they check the code, perform analysis without changing the code.

Intnetion for this type of separation is to clearly communicate what user can expect from a particular check.

### Running Checks Before each Commit

In order to add quality code checker as mandatory step prior to code being committed, you can use `pre-commit` hook. Open:

```bash
vi .git/hooks/pre-commit
```

and put:

```php
#!/usr/bin/php
<?php

$qc_file_path = dirname(__DIR__, 2) . '/.php_qc.php';

if (is_file($qc_file_path)) {
    print "Quality checker found at {$qc_file_path}. Running checks...\n\n";

    try {
        $quality_checker = require_once $qc_file_path;
        $quality_checker->check();
    } catch (\Throwable $e) {
        $quality_checker->communicateFailure($e);
        
        exit(1);
    }
} else {
    print "Quality checker not found at {$qc_file_path}\n";
}

exit(0);
```

Don't forget to set `pre-commit` hook as executable:

```bash
chmod +x .git/hooks/pre-commit
```
