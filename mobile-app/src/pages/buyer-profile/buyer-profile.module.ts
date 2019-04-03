import { NgModule } from '@angular/core';
import { OfanCoreShareModule } from '../../module/ofancore-share.module';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';
import { BuyerProfilePage } from './buyer-profile';

@NgModule({
  declarations: [
    BuyerProfilePage,
  ],
  imports: [
    IonicPageModule.forChild(BuyerProfilePage),
    TranslateModule.forChild(),
    OfanCoreShareModule
  ],
})
export class BuyerProfilePageModule {}
