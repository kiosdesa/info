import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';
import { ChooseCategoryPage } from './choose-category';

@NgModule({
  declarations: [
    ChooseCategoryPage,
  ],
  imports: [
    IonicPageModule.forChild(ChooseCategoryPage),
    TranslateModule
  ],
})
export class ChooseCategoryPageModule {}
