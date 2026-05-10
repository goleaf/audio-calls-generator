module.exports = {
    plugins: {
        'postcss-preset-env': {
            autoprefixer: {
                flexbox: 'no-2009',
                grid: 'autoplace',
            },
            features: {
                'custom-properties': false,
                'logical-properties-and-values': true,
                'media-query-ranges': true,
                'nesting-rules': true,
            },
            stage: 3,
        },
    },
};
