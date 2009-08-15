# Page Templates extension

- Version: 0.65
- Author: craig zheng
- Build Date: 13th June 2009
- Requirements: Symphony 2.0.2 (build 375) from GitHub

A Symphony extension enabling the creation of pages from predefined templates.

## Installation

1. Upload the 'pagetemplates' folder in this archive to your Symphony 'extensions' folder.
2. Go to System > Extensions, select "Page Templates", choose "Enable" from the with-selected menu, then click Apply.

## Usage

### Managing Templates

Page Templates can be managed at `Blueprints > Page Templates`. Creation/editing works almost exactly as with Pages, but with a few notable exceptions:

- `URL Handle` is an optional field. The more important field here is the template's `Title`, which is used to set the filename and to determine if there are duplicate templates.
- Template XSLT files are stored in `/workspace/pages/templates`

### Create Pages From Templates

There are currently two ways to spawn new pages using your templates:

- When browsing the list of available templates, click the **New `Template_Title` Page** link in the "Available Actions" column.
- Immediately after creating a template you will see a **Create Page From Template** link in the page alert.
