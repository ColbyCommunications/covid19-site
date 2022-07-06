const fs = require('fs');
const { exec, execSync } = require('child_process');

const { env } = process;

fs.closeSync(fs.openSync('.env', 'w'));

exec(
    'platform variable:list --no-header --columns=name,value --format=csv',
    (error, stdout, stderr) => {
        if (error) {
            console.log(`error: ${error.message}`);
            return;
        }
        if (stderr) {
            console.log(`stderr: ${stderr}`);
            return;
        }

        var lines = stdout.toString().split('\n');
        var platformVars = {};

        lines.forEach(function (line) {
            var lineArr = line.split(',');
            env[lineArr[0]] = lineArr[1];

            platformVars[lineArr[0]] = lineArr[1];
        });

        const passphrase = platformVars.passphrase;
        delete platformVars.passphrase;

        fs.appendFileSync(
            '.env',
            `PLATFORM_VARS=${JSON.stringify(platformVars).replace(/\s+/g, '')}`
        );
        fs.appendFileSync('.env', `\nPASSPHRASE="${passphrase}"`);
    }
);
