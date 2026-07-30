import esbuild from 'esbuild';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const pagesDir = path.join(root, 'resources/js/Pages');
const extensions = ['', '.js', '.jsx'];

function walk(directory) {
    return fs.readdirSync(directory, { withFileTypes: true }).flatMap((entry) => {
        const absolute = path.join(directory, entry.name);

        if (entry.isDirectory()) {
            return walk(absolute);
        }

        return absolute.endsWith('.jsx') ? [absolute] : [];
    });
}

const pageMap = walk(pagesDir).map((file) => {
    const pagePath = `./Pages/${path.relative(pagesDir, file).replaceAll(path.sep, '/')}`;

    return `${JSON.stringify(pagePath)}: () => import(${JSON.stringify(pagePath)})`;
});

let source = fs.readFileSync(path.join(root, 'resources/js/app.jsx'), 'utf8');
source = source.replace("import '../css/app.css';", '');
source = source.replace(
    "import.meta.glob('./Pages/**/*.jsx')",
    `({${pageMap.join(',')}})`,
);

await esbuild.build({
    stdin: {
        contents: source,
        loader: 'jsx',
        resolveDir: path.join(root, 'resources/js'),
        sourcefile: 'resources/js/app.jsx',
    },
    bundle: true,
    splitting: true,
    format: 'esm',
    outdir: path.join(os.tmpdir(), 'skillup-esbuild-check'),
    jsx: 'automatic',
    logLevel: 'silent',
    plugins: [
        {
            name: 'skillup-alias-and-css-check',
            setup(build) {
                build.onResolve({ filter: /^@\// }, (args) => ({
                    path: resolveImport(path.join(root, 'resources/js', args.path.slice(2))),
                }));

                build.onResolve({ filter: /\.css$/ }, (args) => ({
                    path: args.path,
                    namespace: 'skillup-empty-css',
                }));

                build.onLoad({ filter: /.*/, namespace: 'skillup-empty-css' }, () => ({
                    contents: '',
                    loader: 'css',
                }));
            },
        },
    ],
});

console.log('Inertia React source bundle check passed.');

function resolveImport(basePath) {
    for (const extension of extensions) {
        const candidate = `${basePath}${extension}`;

        if (fs.existsSync(candidate) && fs.statSync(candidate).isFile()) {
            return candidate;
        }
    }

    return basePath;
}
