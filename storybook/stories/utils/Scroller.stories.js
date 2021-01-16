import React from 'react';
import {IframeScroller, DivScroller} from './ScrollerWrapper';
import Content from '../Content.html';
import ContentPageInline from '!!url-loader!../ContentPage.html';
import ContentPage from '!!file-loader!../ContentPage.html';

export default {
    title: 'Utils/Scroller',
    component: DivScroller,
    decorators: [(Story) => <div style={{position: 'relative', border: '1px solid black'}}><Story/></div>],
    argTypes: {
        onError: {action: 'error'}
    },
};

const EmptyTemplate = ({...args}) => <DivScroller {...args} >Empty</DivScroller>;
const DivTemplate = ({...args}) => (
    <DivScroller {...args} >
        <div dangerouslySetInnerHTML={{__html: Content}}/>
    </DivScroller>
);
const IframeTemplate = ({...args}) => <IframeScroller {...args} />;

export const div = DivTemplate.bind({});
div.storyName = 'div';
div.args = {
    speed: 4,
};
export const noScroll = EmptyTemplate.bind({});
noScroll.storyName = 'No Scrolling';
noScroll.args = {
    speed: 4,
};

export const iFrame = IframeTemplate.bind({});
iFrame.storyName = 'iFrame';
iFrame.args = {
    url: ContentPage,
    speed: 4,
};

export const iFrameRestricted = IframeTemplate.bind({});
iFrameRestricted.storyName = 'iFrame Restricted';
iFrameRestricted.args = {
    url: ContentPageInline,
    speed: 4,
};

