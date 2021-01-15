import React from 'react';
import {IframeScroller, DivScroller} from "./ScrollerWrapper";
import Content from "../Content.html";
import ContentPage from "!!url-loader!../ContentPage.html";

export default {
  title: 'Utils/Scroller',
  component: DivScroller,
  decorators: [(Story) => <div style={{ position: 'relative', border: '1px solid black' }}><Story/></div>]
};

const DivTemplate = ({...args}) => <DivScroller {...args} ><div dangerouslySetInnerHTML={{__html: Content}}/></DivScroller>;
const IframeTemplate = ({...args}) => <IframeScroller {...args} />;

export const div = DivTemplate.bind({});
div.storyName = 'div';
div.args = {
  speed: 4,
};

export const iFrame = IframeTemplate.bind({});
iFrame.storyName = 'iFrame';
iFrame.args = {
  url: ContentPage,
  speed: 4,
};

