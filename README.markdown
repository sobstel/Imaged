# Imaged

Imaged is object-oriented wrapper for GD2 library.

It addresses a few issues while working with GD2, e.g. format auto-detecting
(both when reading and writing), easy and flexible resizing and cropping etc.

## Methods

* static create(file)
* __construct(file)
* resample(w, h, [srcX], [srcY], [scrW], [srcH])
* crop(w, h, [x], [y])
* fit(w, h, [method])
* write([filename], [args])
* write([type], [args])
* __call(method, [args])

## Supported formats

Imaged supports most popular web formats, which are JPEG, PNG and GIF. There is no plan for other formats supported by GD (XPM, XBM, WBMP, GD, GD2 are either not widely used or simply old).

## Exceptions codes

- Loading/saving image
  - 11 Provided file does not exist or is not an image
  - 12 Unsupported image type (while reading)
  - 13 Unsupported image type (while writing)
- Operations
  - 21 Operation failed - {func}() returned false
  - 22 Resampling failed - imagecopyresampled() returned false
  - 23 Unsupported method Imaged::{method}()
- Sizes
  - 31 Either width or height must be set
  - 32 Both new width and new height must be greather than 0

