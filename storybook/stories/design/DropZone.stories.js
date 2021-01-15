import React from 'react';

import '../../../Resources/Public/Css/inline_editing.css';

export const DropzoneEnableTemplate = ({children, ...args}) => <div className="dropzones-enabled" {...args}>{children}</div>;
const DivTemplate = ({...args}) => <div {...args} />;

export const DropzoneEnableDecorator = (Story) => <DropzoneEnableTemplate><Story/></DropzoneEnableTemplate>;
export const DropzoneBeforeAfterDecorator = (Story) => <div><div>Before Dropzone</div><Story/><div>After Dropzone</div></div>;

export default {
    title: 'Design/Dropzone',
    excludeStories: ['DropzoneEnableTemplate', 'DropzoneEnableDecorator', 'DropzoneBeforeAfterDecorator']
};

export const simple = DivTemplate.bind({});
simple.decorators = [DropzoneBeforeAfterDecorator];
simple.args = {
  className: 't3-frontend-editing__dropzone',
};

export const enabled = DivTemplate.bind({});
enabled.decorators = [DropzoneEnableDecorator, DropzoneBeforeAfterDecorator];
enabled.args = simple.args;

export const enabledActive = DivTemplate.bind({});
enabledActive.decorators = enabled.decorators;
enabledActive.args = {
  ...enabled.args,
  className: enabled.args.className + ' active',
};

export const enabledHidden = DivTemplate.bind({});
enabledHidden.decorators = enabled.decorators;
enabledHidden.args = {
  ...enabled.args,
  className: enabled.args.className + ' t3-frontend-editing__dropzone-hidden',
};

export const enabledActiveHidden = DivTemplate.bind({});
enabledActiveHidden.decorators = enabled.decorators;
enabledActiveHidden.args = {
  ...enabledActive.args,
  className: enabledActive.args.className + ' t3-frontend-editing__dropzone-hidden',
};

