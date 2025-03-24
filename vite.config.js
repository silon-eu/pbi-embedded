import { resolve } from 'path'

export default {
    root: resolve(__dirname, 'assets/src'),
    build: {
        manifest: 'manifest.json',
        outDir: resolve(__dirname, 'assets/dist'),
        emptyOutDir: true,
        rollupOptions: {
            input: resolve(__dirname, 'assets/src/js/main.js')
        }
    },
    server: {
        port: 3008
    }
}