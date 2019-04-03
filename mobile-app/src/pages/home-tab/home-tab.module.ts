import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { HomeTabPage } from './home-tab';
import { TranslateModule } from '@ngx-translate/core';

@NgModule({
  declarations: [
    HomeTabPage,
  ],
  imports: [
    IonicPageModule.forChild(HomeTabPage),
    TranslateModule.forChild()
  ],
})
export class HomeTabPageModule {}
