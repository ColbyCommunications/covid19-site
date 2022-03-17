const fs = require('fs');

function getArgs() {
    const args = {};
    process.argv.slice(2, process.argv.length).forEach((arg) => {
        // long arg
        if (arg.slice(0, 2) === '--') {
            const longArg = arg.split('=');
            const longArgFlag = longArg[0].slice(2, longArg[0].length);
            const longArgValue = longArg.length > 1 ? longArg[1] : true;
            args[longArgFlag] = longArgValue;
        }
        // flags
        else if (arg[0] === '-') {
            const flags = arg.slice(1, arg.length).split('');
            flags.forEach((flag) => {
                args[flag] = true;
            });
        }
    });
    return args;
}

// get args
const args = getArgs();

fs.readFile('./public/lighthouse/branches.json', (err, data) => {
    if (err) throw err;
    let branches = JSON.parse(data);

    fs.writeFile(
        './public/lighthouse/branches.json',
        JSON.stringify({
            branches: [
                ...branches.branches.filter(function (branch) {
                    return branch.name !== args.branch;
                }),
            ],
        }),
        (err) => {
            if (err) console.log(err);
            else {
                console.log('branches.json written successfully\n');
            }
        }
    );
});
