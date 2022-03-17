const superagent = require('superagent');

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

superagent
    .get(args.url)
    .set('user-agent', 'colby-github')
    .set(
        'Cookie',
        'ColbyAuth=%7B%22email%22%3A%22webmaster%40colby.edu%22%2C%22roles%22%3A%5B%22administrator%22%5D%7D'
    )
    .end((err, res) => {
        switch (Math.floor(res.status / 100)) {
            case 4:
                superagent
                    .post(args.notifyUrl)
                    .send({
                        status: res.status,
                        text: `<!here> The site ${args.url} is returning a 400-level error! Response code: *${res.status}*`,
                    })
                    .set('accept', 'json')
                    .end((err, res) => {
                        console.log(res);
                    });

                break;
            case 5:
                superagent
                    .post(args.notifyUrl)
                    .send({
                        status: res.status,
                        text: `<!here> The site ${args.url} is returning a 500-level error! Response code: *${res.status}*`,
                    })
                    .set('accept', 'json')
                    .end((err, res) => {
                        console.log(res);
                    });
                break;
        }
    });
