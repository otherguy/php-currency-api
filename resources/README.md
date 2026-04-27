# Resources

This folder contains assets used for the project, such as social preview images.

## Open Graph Preview Image

The open graph preview image is provided as an SVG vector file (`open-graph-preview.svg`) to ensure it's easily editable and scalable.

If you need to generate a PNG version of the open graph image (e.g., for platforms that don't support SVG), you can use `npx` (requires Node.js) with the `sharp-cli` tool.

Run the following command in your terminal from the root directory of the project:

```bash
npx -y sharp-cli@latest -i resources/open-graph-preview.svg -o resources/open-graph-preview.png
```

This will correctly read the SVG's 1280x640 dimensions and render a high-quality `open-graph-preview.png` without any cropping or weird padding issues that can happen with built-in OS tools.
